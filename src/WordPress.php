<?php

namespace GeniePress;

use GeniePress\Interfaces\GenieComponent;
use GeniePress\Utilities\HookInto;
use WP;

/**
 * Class WordPress
 * Helper Functions
 *
 * @package GeniePress
 */
class WordPress implements GenieComponent
{

    /**
     * a list of WordPress fields
     *
     * @var string[]
     */
    public static $postFields = [
        'ID',
        'post_author',
        'post_date',
        'post_date_gmt',
        'post_content',
        'post_title',
        'post_excerpt',
        'post_status',
        'comment_status',
        'ping_status',
        'post_password',
        'post_name',
        'to_ping',
        'pinged',
        'post_modified',
        'post_modified_gmt',
        'post_content_filtered',
        'post_parent',
        'guid',
        'menu_order',
        'post_type',
        'post_mime_type',
        'comment_count',
    ];

    /**
     * Query Variables
     *
     * @var array
     */
    protected static $query_vars;



    /**
     * Constructor
     */
    public static function setup()
    {
        // We process all variables here and also capture and query variables.
        HookInto::action('parse_request')
            ->run(function (WP $wp) {
                static::$query_vars = $wp->query_vars;
            });

        // Hook into the views render function and make the session variables available to twig
        HookInto::filter(Genie::hookName('get_site_var'))
            ->run(function ($vars) {
                return array_merge($vars, ['query_vars' => static::$query_vars]);
            });
    }

}
