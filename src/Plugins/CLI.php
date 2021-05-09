<?php

namespace GeniePress\Plugins;

class CLI
{

    /**
     * Check if WordPress CLI is disabled
     *
     * @return bool
     */
    public static function isDisabled(): bool
    {
        return ! static::isEnabled();
    }



    /**
     * Check if WordPress CLI is enabled
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return defined('WP_CLI') && WP_CLI;
    }

}
