<?php

namespace GeniePress\Utilities;

use GeniePress\Cache;
use GeniePress\Tools;
use JsonSerializable;

/**
 * Simple wrapper for WordPress wp_remote_post
 *
 * @package GeniePress
 */
class ApiCall implements JsonSerializable
{

    /**
     * url for the API call
     *
     * @var
     */
    protected $url;

    /**
     * Method
     *
     * @protected string
     */
    protected $method = 'POST';

    /**
     * Timeout in seconds
     *
     * @protected int
     */
    protected $timeout = 10;

    /**
     * How many redirections allowed?
     *
     * @protected int
     */
    protected $redirection = 5;

    /**
     * Which http version to use?
     *
     * @protected string
     */
    protected $httpVersion = '1.0';

    /**
     * Should this API call block script execution?
     *
     * @protected bool
     */
    protected $blocking = true;

    /**
     * An array of additional headers to send
     *
     * @protected array
     */
    protected $headers = [];

    /**
     * API call body
     *
     * @protected array
     */
    protected $body = [];

    /**
     * Data format
     *
     * @protected string
     */
    protected $data_format = 'query';

    /**
     * An Array of cookies to send with the request
     *
     * @protected array
     */
    protected $cookies = [];

    /**
     * The response
     *
     * @protected array
     */
    protected $response = [];

    /**
     * Should this API call be cached?
     *
     * @protected bool
     */
    protected $cache = false;

    /**
     * How long should the data be cached for?
     *
     * @protected int
     */
    protected $cacheFor = 300;



    /**
     * HttpClient constructor.
     *
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }



    /**
     * GET shortcut
     *
     * @param  string  $url
     * @param  array  $vars
     *
     * @return array|bool|object
     */
    public static function get(string $url, array $vars = [])
    {
        $call = static::to($url)
            ->usingMethod('GET')
            ->withBody($vars)
            ->send();
        if ($call->wasSuccessful()) {
            return $call->getResponseBody();
        }

        return false;
    }



    /**
     * Simple post shortcut
     *
     * @param $url
     * @param $vars
     *
     * @return array|bool|object
     */
    public static function post($url, $vars)
    {
        $call = static::to($url)
            ->withBody($vars)
            ->send();
        if ($call->wasSuccessful()) {
            return $call->getResponseBody();
        }

        return false;
    }



    /**
     * Nice way to initialise
     * $call = API::to('https://www.somedomain.com')
     *   ->body(['a'=>1,'b'=>2 ])
     *   ->send();
     * if ($call->wasSuccessful() {
     * }
     *
     * @param $url
     *
     * @return ApiCall
     */
    public static function to($url): ApiCall
    {
        return new static($url);
    }



    /**
     * Add a header to the API call
     *
     * @param $header
     * @param $data
     *
     * @return $this
     */
    public function addHeader($header, $data): ApiCall
    {
        $this->headers[$header] = $data;

        return $this;
    }



    /**
     * Add Multiple Headers
     *
     * @param $headers
     *
     * @return $this
     */
    public function addHeaders($headers): ApiCall
    {
        foreach ($headers as $header => $data) {
            $this->addHeader($header, $data);
        }

        return $this;
    }



    /**
     * Cache Results
     *
     * @param  int  $seconds
     *
     * @return $this
     */
    public function cacheFor(int $seconds): ApiCall
    {
        $this->enableCache($seconds);

        return $this;
    }



    /**
     * Disable the Cache
     *
     * @return $this
     */
    public function disableCache(): ApiCall
    {
        $this->cache = false;

        return $this;
    }



    /**
     * Cache Results
     *
     * @param  int  $seconds
     *
     * @return $this
     */
    public function enableCache(int $seconds = 3600 * 12): ApiCall
    {
        $this->cache    = true;
        $this->cacheFor = $seconds;

        return $this;
    }



    /**
     * Returns true if the APi called failed
     *
     * @return bool
     */
    public function failed(): bool
    {
        if (is_wp_error($this->response)) {
            return true;
        }
        $code = $this->response['response']['code'];

        return $code < 200 or $code > 299;
    }



    /**
     * Returns the full response
     *
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }



    /**
     * Gets the response body
     *
     * @return array|bool|object
     */
    public function getResponseBody()
    {
        if ( ! isset($this->response['body'])) {
            return false;
        }

        return Tools::maybeConvertFromJson($this->response['body']);
    }



    /**
     * Get Response Code
     *
     * @return string|bool
     */
    public function getResponseCode()
    {
        return isset($this->response['response']) ? $this->response['response']['code'] : false;
    }



    /**
     * Get response headers
     *
     * @return bool|mixed
     */
    public function getResponseHeaders()
    {
        return $this->response['headers'] ?? false;
    }



    /**
     * get response Message
     *
     * @return string|bool
     */
    public function getResponseMessage()
    {
        return isset($this->response['response']) ? $this->response['response']['message'] : false;
    }



    public function jsonSerialize(): array
    {
        return [
            'success' => $this->wasSuccessful(),
            'code'    => $this->getResponseCode(),
            'body'    => $this->getResponseBody(),
        ];
    }



    /**
     * Do the API call
     *
     * @return $this
     */
    public function send(): ApiCall
    {
        // Build a Transient Key
        $key = Cache::getCachePrefix().'_api_'.md5(serialize([
                'url'         => $this->url,
                'method'      => $this->method,
                'timeout'     => $this->timeout,
                'redirection' => $this->redirection,
                'httpversion' => $this->httpVersion,
                'blocking'    => $this->blocking,
                'headers'     => $this->headers,
                'body'        => $this->body,
                'cookies'     => $this->cookies,
                'data_format' => $this->data_format,
            ]));

        if ( ! $this->cache || ($this->response = get_transient($key)) === false) {
            // It wasn't there, so regenerate the data and save the transient
            $this->response = wp_remote_post($this->url, [
                    'method'      => $this->method,
                    'timeout'     => $this->timeout,
                    'redirection' => $this->redirection,
                    'httpversion' => $this->httpVersion,
                    'blocking'    => $this->blocking,
                    'headers'     => $this->headers,
                    'body'        => $this->body,
                    'cookies'     => $this->cookies,
                    'data_format' => $this->data_format,
                ]
            );
            if ($this->cache) {
                set_transient($key, $this->response, $this->cacheFor);
            }
        }

        return $this;
    }



    /**
     * Set the data Format
     *
     * @param $format
     *
     * @return $this
     */
    public function setDataFormat($format): ApiCall
    {
        $this->data_format = $format;

        return $this;
    }



    /**
     * Set the Method of this call
     *
     * @param $method
     *
     * @return $this
     */
    public function usingMethod($method): ApiCall
    {
        $this->method = $method;

        return $this;
    }



    /**
     * Returns true if the APi called was Successful
     *
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        return ! $this->failed();
    }



    /**
     * Add the Body for the API call
     *
     * @param $body
     *
     * @return $this
     */
    public function withBody($body): ApiCall
    {
        $this->body = $body;

        return $this;
    }



    /**
     * Add a json Body. cannot be used at the same time as withBody()
     *
     * @param $json
     *
     * @return $this
     */
    public function withJson($json): ApiCall
    {
        $this->withBody(Tools::maybeConvertToJson($json));
        $this->setDataFormat('body');
        $this->addHeader('Content-Type', 'application/json; charset=utf-8');

        return $this;
    }

}
