<?php

namespace GeniePress;

/**
 * Class Options
 * maintain options in wp_options table
 *
 * @package GeniePress
 */
class Options
{

    /**
     * options array
     *
     * @var null
     */
    private static $options = null;



    /**
     * get an option
     *
     * @param  string  $option
     * @param  mixed  $default
     *
     * @return bool|mixed
     */
    public static function get(string $option, $default = false)
    {
        static::load();
        if ( ! isset(static::$options[$option])) {
            return $default;
        }

        return static::$options[$option];
    }



    /**
     * set an option
     *
     * @param  string  $option
     * @param  mixed  $value
     */
    public static function set(string $option, $value)
    {
        static::load();
        static::$options[$option] = $value;
        static::save();
    }



    /**
     * get the key used for options
     *
     * @return string
     */
    protected static function getKey(): string
    {
        return apply_filters(Genie::hookName('option_key'), 'genie_options');
    }



    /**
     * load options into memory
     */
    protected static function load()
    {
        if (is_null(static::$options)) {
            static::$options = get_option(static::getKey());
        }
    }



    /**
     * Save options
     */
    protected static function save()
    {
        update_option(static::getKey(), static::$options);
    }

}
