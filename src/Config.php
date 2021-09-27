<?php

namespace GeniePress;

/**
 * Wrapper to wp-config.php
 * Class Config
 */
class Config
{

    /**
     * Get a config value
     *
     * @param  string  $constant
     * @param  mixed  $default
     *
     * @return bool|mixed
     */
    public static function get(string $constant, $default = false)
    {
        if ( ! defined($constant)) {
            return $default;
        }

        return constant($constant);
    }

}
