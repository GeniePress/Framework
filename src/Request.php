<?php

namespace GeniePress;

class Request
{

    /**
     * Input variables
     *
     * @var null
     */
    protected static $data;

    /**
     * was JSON data received in the body?
     *
     * @var bool
     */
    protected static $receivedJson = false;

    /**
     * Was the json received valid ?
     *
     * @var bool
     */
    protected static $jsonValid = true;



    /**
     * Get a value from the input
     *
     * @param  string  $var
     * @param  mixed  $default
     *
     * @return mixed
     */
    public static function get(string $var, $default = false)
    {
        if (static::has($var)) {
            return static::$data[$var];
        }

        return $default;
    }



    /**
     * Return data from the request
     *
     * @return null
     */
    public static function getData()
    {
        return static::$data;
    }



    /**
     * Does the request contain a variable?
     *
     * @param $var
     *
     * @return bool
     */
    public static function has($var): bool
    {
        static::maybeParseInput();

        return isset(static::$data[$var]);
    }



    /**
     * Collect Data from various input mechanisms
     */
    public static function maybeParseInput(): void
    {
        // Done already?
        if ( ! is_null(static::$data)) {
            return;
        }

        static::$data = [];

        $body = file_get_contents('php://input');

        if ($body) {
            [static::$receivedJson, static::$jsonValid] = Tools::isValidJson($body);
            if (static::$receivedJson && static::$jsonValid) {
                static::$data = array_merge(static::$data, json_decode($body, true));
            }
        }

        if ( ! empty($_GET)) {
            static::$data = array_merge(static::$data, Tools::stripSlashesArray($_GET));
        }

        if ( ! empty($_POST)) {
            static::$data = array_merge(static::$data, Tools::stripSlashesArray($_POST));
        }
    }



    /**
     * Get the request method
     *
     * @return string
     */
    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }



    /**
     * Check if we received a json body
     *
     * @return bool
     */
    public static function wasJsonReceived(): bool
    {
        return static::$receivedJson;
    }



    /**
     * was the json received invalid ?
     *
     * @return bool
     */
    public static function wasJsonReceivedInvalid(): bool
    {
        return ! static::$jsonValid;
    }



    /**
     * Check if we received valid Json
     *
     * @return bool
     */
    public static function wasJsonReceivedValid(): bool
    {
        return static::$jsonValid;
    }

}
