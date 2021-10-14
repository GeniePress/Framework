<?php

namespace GeniePress\Components;

use GeniePress\Genie;
use GeniePress\Utilities\HookInto;
use Throwable;

/**
 * A simple templating engine for Genie.
 *
 * ```php
 * layout.php :
 *   <h2>This is a template</h2>
 *   {% yield content %}
 *
 * template.php
 *   {% include layouts/layout.php %}
 *   {% block content %}
 *   <p>Extends content block!</p>
 *   <p>Your name is {{ $var }}</p>
 *   <p> Safe var {{{ $var }}}</p>
 *   <p>{% echo __("I can include php too!") %}</p>
 *   <ul>
 *   {% foreach($array as $element): %}
 *      <li>{{ $element }}</li>
 *   {% endforeach: %}
 *   </ul>
 *   [shortcode var1="{{ $var }}"]
 *   {% endblock %}
 *
 * Template::with('template.php')
 *   ->addVars([
 *      'var' => 'something',
 *      'array' => [1,2,3,4]
 *   ])
 *   ->display();
 * ```
 *
 */
class Template
{

    /**
     * Is Cache enabled?. Individual
     *
     * @var bool
     */
    protected static $cache = true;

    /**
     * Is Debug enabled?
     *
     * @var bool
     */
    protected static $debug = false;

    /**
     * Full path name of the cache folder
     * @var string
     */
    protected static $cacheFolder = '';

    /**
     * An array of folders to check for the view. These are added to the include path
     * @var array
     */
    protected static $viewFolders = [];

    /**
     * An array of key value pairs sent to the template
     *
     * @var array
     */
    protected $vars = [];

    /**
     * The template.
     *
     * @var string
     */
    protected $template;

    /**
     * Should we process shortcodes on the template?
     *
     * @var bool
     */
    protected $processShortcodes = true;

    /**
     * The last time one of the files for this template was modified
     * @var int
     */
    protected $lastModifiedTime = 0;

    /**
     * A list of all templates used by this template
     * @var array
     */
    protected $templates = [];

    /**
     * Should this template be cached?
     * @var array
     */
    protected $cacheTemplate = true;

    /**
     * Should this template be debugged?
     * @var array
     */
    protected $debugTemplate = false;



    /**
     * Our main setup
     *
     * @param  string  $templateFolder  override the default template folder. Provide the full path
     * @param  bool  $cache  Should templates be cached ?
     * @param  string  $cacheFolder  override the default cache folder. Provide the full path
     */
    public static function setup(string $templateFolder = '', bool $cache = true, string $cacheFolder = '', bool $debug = false): void
    {
        // Hook Into the Init action
        HookInto::action('init', 1)
            ->run(function () use ($cacheFolder, $cache, $debug, $templateFolder) {
                if ($cacheFolder) {
                    static::$cacheFolder = $cacheFolder;
                }

                if ($templateFolder) {
                    static::$viewFolders[] = $templateFolder;
                }

                // Load folder from plugin or theme
                $defaultFolder = Genie::getFolder().'/src/templates';
                if (file_exists($defaultFolder)) {
                    static::$viewFolders[] = $defaultFolder;
                }

                static::$debug       = apply_filters(Genie::hookName('template_debug'), $debug);
                static::$cache       = apply_filters(Genie::hookName('template_cache'), $cache);
                static::$viewFolders = apply_filters(Genie::hookName('template_folders'), static::$viewFolders);

                foreach (static::$viewFolders as $folder) {
                    if (is_dir($folder)) {
                        set_include_path(get_include_path().PATH_SEPARATOR.$folder);
                    }
                }
            });

        HookInto::action(Genie::hookName('after_deploy'))->run(function () {
            static::clearCache();
        });
    }



    /**
     * View constructor.
     *
     * @param  string  $template
     */
    public function __construct(string $template)
    {
        $this->template      = $template;
        $this->cacheTemplate = static::$cache;
        $this->debugTemplate = static::$debug;
    }



    /**
     * Clear template cache
     */
    public static function clearCache(): void
    {
        $cacheFolder = trailingslashit(static::getCacheFolder());

        foreach (glob($cacheFolder.'*') as $file) {
            unlink($file);
        }
    }



    /**
     * Static constructor
     * Which template to use?  This could be a file or a string
     *
     * @param $template
     *
     * @return static
     */
    public static function with($template): Template
    {
        return new static($template);
    }



    /**
     * Add a variable to be sent to template
     *
     * @param $var
     * @param $value
     *
     * @return $this
     */
    public function addVar($var, $value): Template
    {
        $this->vars[$var] = $value;

        return $this;
    }



    /**
     * Add variables to the twig template
     *
     * @param  array  $fields
     *
     * @return $this
     */
    public function addVars(array $fields): Template
    {
        $this->vars = array_merge($this->vars, $fields);

        return $this;
    }



    /**
     * Should this template be cached?
     * can be used to override the default.
     *
     * @param  bool  $cache
     *
     * @return $this
     */
    public function cache(bool $cache): Template
    {
        $this->cacheTemplate = $cache;

        return $this;
    }



    /**
     * Should this template be debugged??
     * can be used to override the default.
     *
     * @param  bool  $debug
     *
     * @return $this
     */
    public function debug(bool $debug): Template
    {
        $this->debugTemplate = $debug;

        return $this;
    }



    /**
     * do not process shortcodes
     *
     * @return $this
     */

    public function disableShortcodes(): Template
    {
        $this->processShortcodes = false;

        return $this;
    }



    /**
     * Output the view rather than return it.
     */
    public function display(): void
    {
        echo $this->render();
    }



    /**
     * Enabled shortcode on this template
     *
     * @return $this
     */
    public function enableShortcodes(): Template
    {
        $this->processShortcodes = true;

        return $this;
    }



    public function render(): string
    {
        try {
            // Process all the files included in this template - This also sets the
            $code = $this->processTemplate($this->template);

            $path = $this->getPath();

            // What filename will we be using?
            $cachedFileName = static::getCacheFolder().md5($path).'.php';

            // Should we generate php code for this template?
            $generateCode = true;

            // If cache is on, and there is a cache file, and the cache file date is after the last modified date for all
            // files referenced by this template.
            // Note: If we're in debug mode, don't cache the template.
            if ( ! $this->debugTemplate && $this->cacheTemplate && file_exists($cachedFileName) && filemtime($cachedFileName) > $this->lastModifiedTime) {
                $generateCode = false;
            }

            if ($generateCode) {
                $startTime = hrtime(true);

                // Process Blocks
                preg_match_all('/{% ?block ?(.*?) ?%}(.*?){% ?endblock ?%}/is', $code, $matches, PREG_SET_ORDER);

                $blocks = [];
                foreach ($matches as $value) {
                    if ( ! array_key_exists($value[1], $blocks)) {
                        $blocks[$value[1]] = '';
                    }
                    if (strpos($value[2], '@parent') === false) {
                        $blocks[$value[1]] = $value[2];
                    } else {
                        $blocks[$value[1]] = str_replace('@parent', $blocks[$value[1]], $value[2]);
                    }
                    $code = str_replace($value[0], '', $code);
                }

                // now replace the block locations
                foreach ($blocks as $block => $value) {
                    $code = preg_replace('/{% ?yield ?'.$block.' ?%}/i', $value, $code);
                }
                $code = preg_replace('/{% ?yield ?(.*?) ?%}/i', '', $code);

                // handle {{{ }}}
                $code = preg_replace('/{{{\s*(.+?)\s*}}}/s', '<?php echo htmlentities($1, ENT_QUOTES, "UTF-8"); ?>', $code);

                // handle {{ }}
                $code = preg_replace('/{{\s*(.+?)\s*}}/s', '<?php echo $1; ?>', $code);

                // handle {% %}
                $code = preg_replace('/{%\s*(.+?)\s*%}/s', '<?php $1; ?>', $code);

                $endTime = hrtime(true);

                $meta = [
                    'Template'      => $this->template,
                    'Path'          => $this->getPath(),
                    'TemplatesUsed' => implode(',', $this->templates),
                    'ViewFolders'   => implode(',', static::$viewFolders),
                    'CacheFolder'   => static::$cacheFolder,
                    'CreatedAt'     => gmdate('jS F Y g:i a'),
                    'CreatedIn'     => (($endTime - $startTime) / 1e+6).' milliseconds',
                ];

                // Stash the file
                $lines   = [];
                $lines[] = '<?php defined( "ABSPATH" ) || exit;';
                $lines[] = '/**';
                foreach ($meta as $key => $value) {
                    $lines[] = " * ".str_pad($key, 20, '.').": ".$value;
                }
                $lines[] = ' */';
                $lines[] = '?>';
                if ($this->debugTemplate) {
                    $lines[] = '<!-- Start Template Debug -->';
                    foreach ($meta as $key => $value) {
                        $lines[] = '<!-- '.str_pad($key, 20, '.').": ".$value.'-->';
                    }
                    $lines[] = '<!-- End Template Debug -->';
                    $lines[] = '<script>';
                    $lines[] = 'console.log('.json_encode($meta).')';
                    $lines[] = '</script>';
                }
                $lines[] = $code;

                $phpCode = implode(PHP_EOL, $lines);
                // Stash the file, even if we're not caching it.
                if ($this->cacheTemplate && static::$cache && ! $this->debugTemplate) {
                    file_put_contents($cachedFileName, $phpCode);
                }
            } else {
                $phpCode = file_get_contents($cachedFileName);
            }

            ob_start();
            extract($this->vars, EXTR_SKIP);
            eval('?>'.$phpCode);
            $html = ob_get_clean();

            if ($this->processShortcodes) {
                $html = do_shortcode($html);
            }

            return $html;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }



    /**
     * Get the full path for this template.
     *
     * @return string
     */
    protected function getPath(): string
    {
        return stream_resolve_include_path($this->template);
    }



    /**
     * bring in all the files.
     *
     * @param  string  $file
     *
     * @return string
     */
    protected function processTemplate(string $file): string
    {
        $file = stream_resolve_include_path($file);

        $this->templates[] = $file;

        $fileModifiedTime = filemtime($file);
        if ($fileModifiedTime > $this->lastModifiedTime) {
            $this->lastModifiedTime = $fileModifiedTime;
        }

        $code = file_get_contents($file, true);
        preg_match_all('/{% ?(extends|include) ?\'?(.*?)\'? ?%}/i', $code, $matches, PREG_SET_ORDER);
        foreach ($matches as $value) {
            $code = str_replace($value[0], static::processTemplate($value[2]), $code);
        }

        return preg_replace('/{% ?(extends|include) ?\'?(.*?)\'? ?%}/i', '', $code);
    }



    /**
     * Get cache folder for Twig
     *
     * @return string
     */
    protected static function getCacheFolder(): string
    {
        if ( ! static::$cacheFolder) {
            $upload              = wp_upload_dir();
            $upload_dir          = $upload['basedir'];
            static::$cacheFolder = apply_filters(Genie::hookName('template_cache_folder'), $upload_dir.'/template_cache');

            // Dir does not exist // OR can't be created? turn off cache.
            if ( ! file_exists(static::$cacheFolder) && ! mkdir(static::$cacheFolder, 0755) && ! is_dir(static::$cacheFolder)) {
                static::$cache = false;
            }
        }

        return trailingslashit(static::$cacheFolder);
    }

}
