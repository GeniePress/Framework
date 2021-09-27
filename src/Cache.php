<?php

namespace GeniePress;

/**
 * Class Cache
 *
 * @package GeniePress
 */
class Cache
{

    /**
     * Clear any api call cache
     */
    public static function clearApiCache(): void
    {
        global $wpdb;

        $prefix = static::getCachePrefix();

        // Delete api cache
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name like '%{$prefix}_api%'  ");
    }



    /**
     * Clear Cache
     *
     * @param  int|array|null  $id
     */
    public static function clearPostCache($id = null): void
    {
        global $wpdb;

        $where = '';
        if (is_array($id)) {
            $where = 'and post_id in ('.implode(',', $id).')';
        } elseif (is_int($id)) {
            $where = 'and post_id = '.$id;
        }

        $prefix = static::getCachePrefix();

        // Delete post cache
        $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '$prefix%'  $where");
    }



    /**
     * Cache Key prefix is used for post_meta cache
     *
     * @return string
     */
    public static function getCachePrefix(): string
    {
        return apply_filters(Genie::hookName('get_cache_prefix'), 'gcache');
    }
}
