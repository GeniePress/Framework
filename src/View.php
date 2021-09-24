<?php

namespace GeniePress;

use GeniePress\Interfaces\GenieComponent;
use GeniePress\Utilities\AddShortcode;
use GeniePress\Utilities\HookInto;
use Throwable;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\Source;
use Twig\TwigFilter;

/**
 * Class View
 * Wrapper around twig
 *
 * @package GeniePress
 */
class View implements GenieComponent
{

    /**
     * Twig Object
     *
     * @var Environment
     */
    protected static $twig;

    /**
     * an array of key value pairs sent to the twig template
     *
     * @var array
     */
    protected $vars = [];

    /**
     * The twig template. This could be a filename or a string.
     *
     * @var string
     */
    protected $template;

    /**
     * Should we process shortcodes in the template?
     *
     * @var bool
     */
    protected $processShortcodes = true;

    /**
     * The type of template we're processing
     *
     * @var string
     */
    protected $templateType = 'file';



    /**
     * Add in our hooks and shortcodes
     */
    public static function setup()
    {
        // Note the sequence. this runs before anything else
        HookInto::action('init', 1)
            ->run(function () {
                $debug     = apply_filters(Genie::hookName('view_debug'), WP_DEBUG);
                $cache     = apply_filters(Genie::hookName('view_cache'), ! WP_DEBUG);
                $pathArray = apply_filters(Genie::hookName('view_folders'), Registry::get('genie_config', 'view_folders'));

                $fileLoader = new FilesystemLoader($pathArray);
                $loader     = new ChainLoader([$fileLoader]);

                $configArray = [
                    'autoescape'  => false,
                    'auto_reload' => true,
                ];

                if ($debug) {
                    $configArray['debug'] = true;
                }
                if ($cache) {
                    $configArray['cache'] = static::getCacheFolder();
                }

                $twig = new Environment($loader, $configArray);

                if ($debug) {
                    $twig->addExtension(new DebugExtension());
                }
                $filter = new TwigFilter('json', Tools::class.'::jsonSafe');
                $twig->addFilter($filter);

                $filter = new TwigFilter('wpautop', 'wpautop');
                $twig->addFilter($filter);

                self::$twig = apply_filters(Genie::hookName('view_twig'), $twig);
            });

        // The shortcode genie_view allows you to have a twig template embedded in content
        AddShortcode::called('genie_view')->run(
            function ($incomingAttributes, $content) {
                $attributes = shortcode_atts([
                    'view' => '',
                ], $incomingAttributes);

                if ( ! $attributes->view) {
                    if (isset($incomingAttributes[0])) {
                        $attributes['view'] = $incomingAttributes[0];
                    } else {
                        $attributes['view'] = $content;
                    }
                }

                return static::with($attributes->view)
                    ->addVars($attributes)
                    ->render();
            });
    }



    /**
     * View constructor.
     *
     * @param  string  $template
     */
    function __construct(string $template)
    {
        $this->template     = $template;
        $this->templateType = substr(strtolower($template), -5) === '.twig' ? 'file' : 'string';
        $this->cache        = ! WP_DEBUG;
        $this->debug        = WP_DEBUG;
    }



    /**
     * Check if a string is valid Syntax
     *
     * @param $string
     *
     * @return bool
     */
    public static function isValidTwig($string): bool
    {
        try {
            static::$twig->tokenize(new Source($string, 'isValidTwig'));

            return true;
        } catch (SyntaxError $e) {
            return false;
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
    public static function with($template): View
    {
        return new static($template);
    }



    /**
     * Add a variable to be sent to twig
     *
     * @param $var
     * @param $value
     *
     * @return $this
     */
    public function addVar($var, $value): View
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
    public function addVars(array $fields): View
    {
        $this->vars = array_merge($this->vars, $fields);

        return $this;
    }



    /**
     * do not process shortcodes
     *
     * @return $this
     */

    function disableShortcodes(): View
    {
        $this->processShortcodes = false;

        return $this;
    }



    /**
     * Output the view rather than return it.
     */
    public function display()
    {
        echo $this->render();
    }



    /**
     * Enabled shortcode on this template
     *
     * @return $this
     */
    function enableShortcodes(): View
    {
        $this->processShortcodes = true;

        return $this;
    }



    /**
     * Render a twig Template
     *
     * @return string
     */
    public function render(): string
    {
        $site = apply_filters(Genie::hookName('get_site_var'), [
            'urls' => [
                'theme' => get_stylesheet_directory_uri(),
                'ajax'  => admin_url('admin-ajax.php'),
                'home'  => home_url(),
            ],
        ]);

        $vars = array_merge(['_site' => $site], $this->vars);
        $vars = apply_filters(Genie::hookName('view_variables'), $vars);

        try {
            if ($this->templateType === 'string') {
                $template = static::$twig->createTemplate($this->template);
                $html     = $template->render($vars);
            } else {
                $html = static::$twig->render($this->template, $vars);
            }

            if ($this->processShortcodes) {
                $html = do_shortcode($html);
            }

            return $html;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }



    /**
     * Get cache folder for Twig
     *
     * @return string
     */
    protected static function getCacheFolder(): string
    {
        $upload     = wp_upload_dir();
        $upload_dir = $upload['basedir'];

        return apply_filters(Genie::hookName('view_cache_folder'), $upload_dir.'/twig_cache');
    }

}
