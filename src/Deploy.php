<?php

namespace GeniePress;

use GeniePress\Interfaces\GenieComponent;
use GeniePress\Plugins\CLI;
use WP_CLI;

/**
 * Class Deploy
 *
 * @package GeniePress
 */
class Deploy implements GenieComponent
{

    /**
     * Setup
     *
     */
    public static function setup()
    {
        if (CLI::isEnabled()) {
            WP_CLI::add_command('deploy', [static::class, 'deploy']);
        }
    }



    /**
     * Run the Deployment Process
     */
    public static function deploy()
    {
        static::log('Starting Deployment');
        do_action('genie_before_deploy');
        static::log('Updating Tables');
        static::updateDatabase();
        static::log('Loading Releases');
        static::loadReleases();
        static::log('Clearing Cache');
        Cache::clearPostCache();
        Cache::clearAPiCache();
        static::log('Flushing rewrite rules');
        flush_rewrite_rules(true);
        do_action('genie_after_deploy');
        static::log('Deployment Complete');
    }



    /**
     * Log progress
     *
     * @param $message
     */
    static function log($message)
    {
        if (CLI::isEnabled()) {
            WP_CLI::log($message);
        }
    }



    /**
     * Load and Run Releases
     */
    protected static function loadReleases()
    {
        $releaseFolder = apply_filters('genie_release_folder', Registry::get('genie_release_folder'));

        if ( ! $releaseFolder || ! file_exists($releaseFolder)) {
            return;
        }

        $releases = Options::get('genie_releases', []);

        foreach (glob(trailingslashit($releaseFolder).'*.php') as $file) {
            if ( ! in_array($file, $releases)) {
                $releases[] = $file;
                require_once($file);
            }
        }
        Options::set('genie_releases', $releases);
    }



    /**
     * Update the database
     */
    protected static function updateDatabase()
    {
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        $sqlStatements = apply_filters('genie_update_database', []);
        foreach ($sqlStatements as $sqlStatement) {
            dbDelta($sqlStatement);
        }
    }

}
