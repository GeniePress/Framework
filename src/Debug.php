<?php

namespace GeniePress;

use JsonException;

/**
 * Class Debug
 *
 * @package GeniePress
 */
class Debug
{

    /**
     * Dump a variable to the console
     *
     * @param  mixed  $var
     *
     * @throws JsonException
     */
    public static function console($var): void
    {
        if (is_array($var) || is_object($var)) {
            $var = json_encode($var, JSON_THROW_ON_ERROR);
        } else {
            $var = "\"$var\"";
        }
        echo "<script>console.log($var)</script>";
    }



    /**
     * Dump a variable
     *
     * @param $var
     */
    public static function d($var): void
    {
        if (is_array($var) || is_object($var)) {
            $var = print_r($var, true);
        }
        print "<pre>$var</pre>";
    }



    /**
     * Dump and die
     *
     * @param $var
     */
    public static function dd($var): void
    {
        self::d($var);
        exit;
    }

}
