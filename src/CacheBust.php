<?php

namespace GeniePress;

use DateTime;
use Exception;
use GeniePress\Interfaces\GenieComponent;
use GeniePress\Utilities\HookInto;
use WP_Scripts;

/**
 * Class CacheBust
 *
 * @package GeniePress
 */
class CacheBust implements GenieComponent
{

    /**
     * Main WordPress Hook for the Theme
     *
     * @throws Exception
     */
    public static function setup()
    {
        HookInto::filter('script_loader_src')
            ->orFilter('style_loader_src')
            ->run([static::class, 'cacheSrc']);
    }



    /**
     * Append the date and time at the end of enqueued scripts & styles, so we can cache bust !
     *
     * @param  string  $src
     *
     * @return string
     * @throws Exception
     */
    public static function cacheSrc(string $src = ''): string
    {
        global $wp_scripts;

        // If $wp_scripts hasn't been initialized then bail.
        if ( ! $wp_scripts instanceof WP_Scripts) {
            return $src;
        }

        // Check if script lives on this domain. Can't rewrite external scripts, they won't work.
        $base_url = apply_filters('genie_cache_busting_path_base_url', $wp_scripts->base_url, $src);
        if ( ! strstr($src, $base_url)) {
            return $src;
        }

        // Remove the 'ver' query var: ?ver=0.1
        $src   = remove_query_arg('ver', $src);
        $regex = '/'.preg_quote($base_url, '/').'/';
        $path  = preg_replace($regex, '', $src);
        $file  = null;

        // If the folder starts with wp- then we can figure out where it lives on the filesystem
        if (strstr($path, '/wp-')) {
            $file = untrailingslashit(ABSPATH).$path;
        }
        if ( ! file_exists($file)) {
            return $src;
        }
        $time_format   = apply_filters('genie_loader_src_time_format', 'Y-m-d_G-i');
        $modified_time = filemtime($file);
        $dt            = new DateTime('@'.$modified_time);
        $time          = $dt->format($time_format);

        return add_query_arg('ver', $time, $src);
    }

}
