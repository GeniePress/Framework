<?php

namespace GeniePress;

/**
 * Class Registry
 * Simple Object Cache
 *
 * @package GeniePress
 */
class Registry
{

    /**
     * array of values
     *
     * @var array
     */
    private static $registry = [];



    /**
     * get a value from the registry
     *
     * @param  string  $group
     * @param  string|null  $index
     * @param  null  $default
     *
     * @return mixed|null
     */
    public static function get(string $group, string $index = null, $default = null)
    {
        if (is_null($index)) {
            if ( ! isset(static::$registry[$group])) {
                static::$registry[$group] = [];
            }

            return static::$registry[$group];
        }

        if (isset(static::$registry[$group][$index])) {
            return static::$registry[$group][$index];
        }

        return $default;
    }



    /**
     * Add a value to a registry array
     *
     * @param  string  $group
     * @param  string  $index
     * @param  mixed  $value
     */
    public static function push(string $group, string $index, $value)
    {
        if ( ! isset(static::$registry[$group])) {
            static::$registry[$group] = [];
        }
        static::$registry[$group][$index] = $value;
    }



    /**
     * Set a value in the registry
     *
     * @param  string  $group
     * @param  mixed  $value
     */
    public static function set(string $group, $value)
    {
        static::$registry[$group] = $value;
    }

}
