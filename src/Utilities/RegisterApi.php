<?php

namespace GeniePress\Utilities;

use GeniePress\ApiHandler;

class RegisterApi
{

    /**
     * the url of the api call
     *
     * @var array
     */
    protected $url;

    /**
     * The accepted Method
     *
     * @var string
     */
    protected $method = 'GET';



    /**
     * constructor.
     *
     * @param  string  $url
     * @param  string  $method
     */
    public function __construct(string $url, string $method = 'GET')
    {
        $this->url    = $url;
        $this->method = $method;
    }



    /**
     * static constructor
     *
     * @param $url
     *
     * @return static
     */
    public static function delete($url): RegisterApi
    {
        return new static($url, 'DELETE');
    }



    /**
     * static constructor
     *
     * @param $url
     *
     * @return static
     */
    public static function get($url): RegisterApi
    {
        return new static($url, 'GET');
    }



    /**
     * static constructor
     *
     * @param $url
     *
     * @return static
     */
    public static function patch($url): RegisterApi
    {
        return new static($url, 'PATCH');
    }



    /**
     * static constructor
     *
     * @param $url
     *
     * @return static
     */
    public static function post($url): RegisterApi
    {
        return new static($url, 'POST');
    }



    /**
     * static constructor
     *
     * @param $url
     *
     * @return static
     */
    public static function put($url): RegisterApi
    {
        return new static($url, 'PUT');
    }



    /**
     * Set the callback and register the actions and filters
     *
     * @param  callable  $callback
     */
    public function run(callable $callback)
    {
        ApiHandler::register($this->url, $this->method, $callback);
    }

}
