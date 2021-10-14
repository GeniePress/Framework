<?php

namespace GeniePress\Components;

use Exception;
use GeniePress\Cache;
use GeniePress\Genie;
use GeniePress\Options;
use GeniePress\Plugins\CLI;
use GeniePress\Registry;
use WP_CLI;

/**
 * Class Deploy
 *
 * @package GeniePress
 */
class Deploy
{

    /**
     * The folder where releases are stored. defaults to src/php/Releases
     * @var string
     */
    protected static $releaseFolder;



    /**
     * Setup
     *
     * @param  string  $releaseFolder
     * @param  string  $command  the name of the wp-cli command to use
     *
     * @throws Exception
     */
    public static function setup(string $releaseFolder = '', string $command = 'deploy'): void
    {
        if ($releaseFolder) {
            static::$releaseFolder = $releaseFolder;
        }

        if (CLI::isEnabled()) {
            WP_CLI::add_command($command, [static::class, 'deploy']);
        }
    }



    /**
     * Run the Deployment Process
     */
    public static function deploy(): void
    {
        static::log('Starting Deployment');
        do_action(Genie::hookName('before_deploy'));
        static::log('Updating Tables');
        static::updateDatabase();
        static::log('Loading Releases');
        static::loadReleases();
        static::log('Clearing Cache');
        Cache::clearPostCache();
        Cache::clearApiCache();
        static::log('Flushing rewrite rules');
        flush_rewrite_rules(true);
        do_action(Genie::hookName('after_deploy'));
        static::log('Deployment Complete');
    }



    /**
     * Log progress
     *
     * @param $message
     */
    public static function log($message): void
    {
        if (CLI::isEnabled()) {
            WP_CLI::log($message);
        }
    }



    /**
     * get the folder where releases are stored
     *
     * @return string
     */
    protected static function getReleaseFolder(): string
    {
        if ( ! static::$releaseFolder) {
            $defaultReleaseFolder  = trailingslashit(Genie::getFolder()).'src/php/Releases';
            static::$releaseFolder = Registry::get('genie_config', 'release_folder', $defaultReleaseFolder);
        }

        return apply_filters(Genie::hookName('release_folder'), static::$releaseFolder);
    }



    /**
     * Load and Run Releases
     */
    protected static function loadReleases(): void
    {
        $releaseFolder = static::getReleaseFolder();

        if ( ! $releaseFolder || ! file_exists($releaseFolder)) {
            return;
        }

        $releases = Options::get('genie_releases', []);

        foreach (glob(trailingslashit($releaseFolder).'*.php') as $file) {
            if ( ! in_array($file, $releases, true)) {
                $releases[] = $file;
                require_once($file);
            }
        }
        Options::set('genie_releases', $releases);
    }



    /**
     * Update the database
     */
    protected static function updateDatabase(): void
    {
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');
        $sqlStatements = apply_filters(Genie::hookName('update_database'), []);
        foreach ($sqlStatements as $sqlStatement) {
            dbDelta($sqlStatement);
        }
    }

}
