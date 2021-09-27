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
    private static $options;



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

        return static::$options[$option] ?? $default;
    }



    /**
     * set an option
     *
     * @param  string  $option
     * @param  mixed  $value
     */
    public static function set(string $option, $value): void
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
    protected static function load(): void
    {
        if (is_null(static::$options)) {
            static::$options = get_option(static::getKey());
        }
    }



    /**
     * Save options
     */
    protected static function save(): void
    {
        update_option(static::getKey(), static::$options);
    }

}
