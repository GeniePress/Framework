<?php

namespace GeniePress;

use GeniePress\Plugins\ACF;
use GeniePress\Traits\HasData;
use GeniePress\Utilities\AddAdminNotice;
use GeniePress\Utilities\HookInto;

/**
 * Class Genie
 *
 * @package GeniePress
 * @property array $components
 * @property string $filename
 * @property string $folder
 * @property string $type
 */
class Genie
{

    use HasData;

    /**
     * Genie constructor.
     *
     * @param  string  $type
     */
    function __construct(string $type = 'theme')
    {
        $filename = '';
        $folder   = '';
        foreach (debug_backtrace() as $trace) {
            if ($trace['file'] !== __FILE__) {
                $filename = $trace['file'];
                $parts    = pathinfo($filename);
                $folder   = $parts['dirname'];
                break;
            }
        }

        $viewFolders = [];

        // Load folder from plugin or theme
        if (file_exists($folder.'/src/twig')) {
            $viewFolders[] = $folder.'/src/twig';
        }

        // Add genie folder last, so they can be overridden
        $viewFolders[] = trailingslashit(dirname(__FILE__)).'Views';

        Registry::push('genie_config','view_folders', $viewFolders);

        $this->fill([
            'components' => [
                WordPress::class,
            ],
            'filename'   => $filename,
            'folder'     => $folder,
            'type'       => $type,
        ]);
    }



    /**
     * What to do on activation
     */
    public static function activation()
    {
        do_action('genie_activation');
        flush_rewrite_rules();
    }



    /**
     * Static Constructor to create a plugin
     *
     * @return Genie
     */
    public static function createPlugin(): Genie
    {
        return new static('plugin');
    }



    /**
     * Static constructor
     *
     * @return Genie
     */
    public static function createTheme(): Genie
    {
        return new static('theme');
    }



    /**
     * What to do on deactivation
     */
    public static function deactivation()
    {
        do_action('genie_deactivation');
    }



    /**
     * Get the list of components registered with Genie
     *
     * @return array
     */
    public static function getComponents(): array
    {
        return Registry::get('genie_config', 'components');
    }



    /**
     * get the filename stored in the registry
     *
     * @return string
     */
    public static function getFilename(): string
    {
        return Registry::get('genie_config', 'filename');
    }



    /**
     * What to do on uninstall
     */
    public static function uninstall()
    {
        do_action('genie_uninstall');
    }



    /**
     * Add a component into Genie
     *
     * @param  string  $component
     *
     * @return $this
     */
    public function addComponent(string $component): Genie
    {
        $components = $this->components;

        if ( ! in_array($component, $components)) {
            $components[] = $component;
        }

        $this->components = $components;

        return $this;
    }



    /**
     * @param  string  $actionName
     *
     * @return Genie
     */
    public function enableAjaxHandler(string $actionName = 'ajax'): Genie
    {
        Registry::push('genie_config','ajax_action', $actionName);

        return $this->addComponent(AjaxHandler::class);
    }



    /**
     * Enable the API handler
     *
     * @param  string  $pathName
     * @param  string  $actionName
     *
     * @return Genie
     */
    public function enableApiHandler(string $pathName = 'api', string $actionName = 'genie_api'): Genie
    {
        Registry::push('genie_config','api_path', $pathName);
        Registry::push('genie_config','action_name', $actionName);

        return $this->addComponent(ApiHandler::class);
    }



    /**
     * Enable Background Jobs
     *
     * @param  string  $variableName  . The name of the variable used to trigger a background job. Defaults to "genie_bj_id"
     *
     * @return Genie
     */
    public function enableBackgroundJobs(string $variableName = 'genie_bj_id'): Genie
    {
        Registry::push('genie_config','bj_id', $variableName);

        return $this->addComponent(BackgroundJob::class);
    }



    /**
     * Enable The Cache Buster
     *
     * @return $this
     */
    public function enableCacheBuster(): Genie
    {
        return $this->addComponent(CacheBust::class);
    }



    /**
     * Enable The Deployment Handler
     *
     * @param  string|null  $folder  a folder relative to your plugin / theme. Defaults to "src/php/Releases"
     *
     * @return $this
     */
    public function enableDeploymentHandler(string $folder = 'src/php/Releases'): Genie
    {
        $folder = trailingslashit($this->folder).$folder;

        if (file_exists($folder)) {
            Registry::push('genie_config','release_folder', $folder);
        }

        return $this->addComponent(Deploy::class);
    }



    /**
     * Enable Genie Session Handler
     *
     * @param  string  $name  the name of the session variable. Defaults to "genie_session"
     *
     * @return $this
     */
    public function enableSessions(string $name = 'genie_session'): Genie
    {
        Registry::push('genie_config','session_name', $name);

        return $this->addComponent(Session::class);
    }



    /**
     * Set the __FILE__ for the plugin - this is needed for activation, deactivation and uninstall hooks
     *
     * @param $filename
     *
     * @return $this
     */
    public function setFilename($filename): Genie
    {
        $this->filename = $filename;

        return $this;
    }



    /**
     * get Genie Going.
     */
    public function start()
    {
        // Load and stash our configuration so we can use it in static methods
        $config = apply_filters('genie_config', $this->getData());
        foreach ($config as $index => $value ) {
            Registry::push('genie_config', $index,$value);
        }

        // Load Required Genie Components
        View::setup();

        // We can't do anything without ACF
        if (ACF::isDisabled()) {
            AddAdminNotice::error('GeniePress requires <a href="https://www.advancedcustomfields.com/">Advanced Custom Fields</a> to be installed and enabled')
                ->isNotDismissible()
                ->display();

            return;
        }

        //Load all our classes.
        if (is_array($config['components'])) {
            foreach ($config['components'] as $class) {
                if (method_exists($class, 'setup')) {
                    $class::setup();
                }
            }
        }

        // Register hooks
        if ($config['type'] === 'plugin') {
            if (isset($config['filename']) && $config['filename']) {
                register_activation_hook($config['filename'], [static::class, 'activation']);
                register_deactivation_hook($config['filename'], [static::class, 'deactivation']);
                register_uninstall_hook($config['filename'], [static::class, 'uninstall']);
            }
        } else {
            HookInto::action('after_setup_theme')
                ->run([static::class, 'activation']);

            HookInto::action('switch_theme')
                ->run([static::class, 'deactivation']);
        }
        // Send a message to the outside world!
        do_action('genie_started');
    }



    /**
     * Set how genie is being used (defaults to plugin)
     *
     * @param  string  $type
     *
     * @return $this
     */
    public function type(string $type): Genie
    {
        $this->type = $type;

        return $this;
    }



    /**
     * Add a bunch of components that should be loaded by Genie
     *
     * @param  array  $components
     *
     * @return $this
     */
    public function withComponents(array $components): Genie
    {
        $this->components = array_merge($this->components, $components);

        return $this;
    }

}
