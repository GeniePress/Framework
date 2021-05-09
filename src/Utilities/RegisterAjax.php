<?php

namespace GeniePress\Utilities;

use GeniePress\AjaxHandler;

class RegisterAjax
{

    /**
     * The url of the ajax call
     *
     * @var array
     */
    protected $url;



    /**
     * constructor.
     *
     * @param  string  $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }



    /**
     * static constructor
     *
     * @param $url
     *
     * @return static
     */
    public static function url($url): RegisterAjax
    {
        return new static($url);
    }



    /**
     * Set the callback and register the actions and filters
     *
     * @param  callable  $callback
     */
    public function run(callable $callback)
    {
        AjaxHandler::register($this->url, $callback);
    }

}
