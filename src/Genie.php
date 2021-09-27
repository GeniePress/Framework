<?php

namespace GeniePress;

use GeniePress\Plugins\ACF;
use GeniePress\Utilities\AddAdminNotice;
use GeniePress\Utilities\HookInto;

/**
 * Class Genie
 *
 * @package GeniePress
 */
class Genie
{

    /**
     * Genie constructor.
     *
     * @param  string  $type  plugin|theme
     */
    public function __construct(string $type = 'theme')
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
        $viewFolders[] = trailingslashit(__DIR__).'Views';

        $this->addComponent(WordPress::class);

        Registry::push('genie_config', 'view_folders', $viewFolders);
        Registry::push('genie_config', 'filename', $filename);
        Registry::push('genie_config', 'folder', $folder);
        Registry::push('genie_config', 'type', $type);
        Registry::push('genie_config', 'hook_prefix', 'genie_');
    }



    /**
     * What to do on activation?
     */
    public static function activation(): void
    {
        do_action(self::hookName('activation'));
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
    public static function deactivation(): void
    {
        do_action(self::hookName('deactivation'));
    }



    /**
     * Get the list of components registered with Genie
     *
     * @return array
     */
    public static function getComponents(): array
    {
        return Registry::get('genie_config', 'components', []);
    }



    /**
     * Get the filename stored in the registry
     *
     * @return string
     */
    public static function getFilename(): string
    {
        return Registry::get('genie_config', 'filename');
    }



    /**
     * Get the folder stored in the registry
     *
     * @return string
     */
    public static function getFolder(): string
    {
        return Registry::get('genie_config', 'folder');
    }



    /**
     * Get the defined Hook prefix.
     *
     * @return string
     */
    public static function getHookPrefix(): string
    {
        return Registry::get('genie_config', 'hook_prefix');
    }



    /**
     * Add the prefix to a hook or filter name
     *
     * @param  string  $name
     *
     * @return string
     */
    public static function hookName(string $name): string
    {
        return static::getHookPrefix().$name;
    }



    /**
     * What to do on uninstall
     */
    public static function uninstall(): void
    {
        do_action(self::hookName('uninstall'));
    }



    /**
     * Add a component into Genie
     *
     * @param  string  $component  classname
     *
     * @return $this
     */
    public function addComponent(string $component): Genie
    {
        $this->withComponents([$component]);

        return $this;
    }



    /**
     * @param  string  $actionName
     *
     * @return Genie
     */
    public function enableAjaxHandler(string $actionName = 'ajax'): Genie
    {
        Registry::push('genie_config', 'ajax_action', $actionName);

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
        Registry::push('genie_config', 'api_path', $pathName);
        Registry::push('genie_config', 'api_action', $actionName);

        return $this->addComponent(ApiHandler::class);
    }



    /**
     * Enable Background Jobs
     *
     * @param  string  $variableName  The name of the variable used to trigger a background job. Defaults to "genie_bj_id"
     *
     * @return Genie
     */
    public function enableBackgroundJobs(string $variableName = 'genie_bj_id'): Genie
    {
        Registry::push('genie_config', 'bj_id', $variableName);

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
        $folder = trailingslashit(static::getFolder()).$folder;

        if (file_exists($folder)) {
            Registry::push('genie_config', 'release_folder', $folder);
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
        Registry::push('genie_config', 'session_name', $name);

        return $this->addComponent(Session::class);
    }



    /**
     * Set the __FILE__ for the plugin - this is needed for activation, deactivation and uninstall hooks
     *
     * @param  string  $filename
     *
     * @return $this
     */
    public function setFilename(string $filename): Genie
    {
        Registry::push('genie_config', 'filename', $filename);

        return $this;
    }



    /**
     * Set the Hook prefix for all actions and filters
     *
     * @param  string  $hook_prefix
     *
     * @return $this
     */
    public function setHookPrefix(string $hook_prefix): Genie
    {
        Registry::push('genie_config', 'hook_prefix', $hook_prefix);

        return $this;
    }



    /**
     * get Genie Going.
     */
    public function start(): void
    {
        do_action(self::hookName('starting'));

        $config = Registry::get('genie_config');

        // Load Required Genie Components
        View::setup();

        // We can't do anything without ACF
        if (ACF::isDisabled()) {
            AddAdminNotice::error(__('GeniePress requires <a href="https://www.advancedcustomfields.com/">Advanced Custom Fields</a> to be installed and enabled'))
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
        do_action(self::hookName('started'));
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
        Registry::push('genie_config', 'type', $type);

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
        Registry::push('genie_config', 'components', array_merge(static::getComponents(), $components));

        return $this;
    }

}
