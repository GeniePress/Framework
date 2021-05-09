<?php

namespace GeniePress;

use GeniePress\Interfaces\GenieComponent;
use GeniePress\Utilities\HookInto;
use Throwable;
use Twig\Environment;
use Twig\TwigFunction;

/**
 * Class AjaxHandler
 *
 * @package GeniePress
 */
class AjaxHandler implements GenieComponent
{

    /**
     * An array of paths to use for ajax calls
     *
     * @var array
     */
    protected static $paths = [];

    /**
     * stash of the string to use for the action
     *
     * @var string
     */
    protected static $action;



    /**
     * Setup Actions, Filters and Shortcodes
     */
    public static function setup()
    {
        /**
         * Handle the ajax call.
         */
        HookInto::action('init')
            ->run(function () {
                $action = static::getActionName();

                HookInto::action('wp_ajax_'.$action)
                    ->orAction('wp_ajax_nopriv_'.$action)
                    ->run(function () use ($action) {
                        Request::maybeParseInput();

                        do_action('genie_received_ajax_request', $action, Request::getData());

                        $requestPath = Request::get('request');

                        if ( ! $requestPath) {
                            Response::error([
                                'message' => "No request specified",
                            ]);
                        }

                        if ( ! static::canHandle($requestPath)) {
                            Response::notFound([
                                'message' => "Request: {$requestPath} not found",
                            ]);
                        }

                        // The Callback exists
                        $callback = static::$paths[$requestPath];
                        $params   = Tools::getCallableParameters($callback);

                        if (is_wp_error($params)) {
                            Response::error([
                                'message' => $params->get_error_message(),
                            ]);
                        }

                        // So we have a nice list of params
                        // was a nonce sent?
                        if ( ! Request::has('nonce')) {
                            Response::error([
                                'message' => 'No nonce specified',
                                'params'  => $params,
                            ]);
                        }

                        if ( ! wp_verify_nonce(Request::get('nonce'), $requestPath)) {
                            Response::error([
                                'message' => 'invalid nonce',
                            ]);
                        }

                        if (Request::wasJsonReceived() && Request::wasJsonReceivedInvalid()) {
                            Response::error([
                                'message'             => "Invalid json received",
                                'json_last_error'     => json_last_error(),
                                'json_last_error_msg' => json_last_error_msg(),

                            ]);
                        }

                        try {
                            $callbackParams = [];

                            foreach ($params as $param) {
                                $name = $param->getName();
                                if ( ! $param->isOptional() && ! Request::has($name)) {
                                    Response::failure(['message' => "required parameter {$name} is missing"]);
                                }
                                $callbackParams[$name] = Request::get($name);
                            }
                            Response::success(call_user_func_array($callback, $callbackParams));
                        } catch (Throwable $error) {
                            $response = [
                                'message' => $error->getMessage(),
                            ];

                            if (method_exists($error, 'getData')) {
                                $response['data'] = $error->getData();
                            }
                            if (WP_DEBUG && method_exists($error, 'getTrace')) {
                                $response['trace'] = $error->getTrace();
                            }

                            Response::failure($response);
                        }
                    });
            });

        /**
         * create the ajax_url function in twig that can prefix the right path, and add the nonce.
         */
        HookInto::filter('genie_view_twig')
            ->run(function (Environment $twig) {
                $function = new TwigFunction('ajax_url', [static::class, 'generateUrl']);
                $twig->addFunction($function);

                return $twig;
            });
    }



    /**
     * Check that a path is registered
     *
     * @param $requestPath
     *
     * @return bool
     */
    public static function canHandle($requestPath): bool
    {
        return isset(static::$paths[$requestPath]);
    }



    /**
     * Generate a url for an ajax call with the $requestPath
     *
     * @param $requestPath
     *
     * @return string
     */
    public static function generateUrl($requestPath): string
    {
        return add_query_arg(
            [
                'nonce'   => wp_create_nonce($requestPath),
                'action'  => static::getActionName(),
                'request' => $requestPath,
            ],
            admin_url('admin-ajax.php')
        );
    }



    /**
     * Register an ajax callback function
     *
     * @param  string  $path
     * @param  callable  $callback
     */
    public static function register(string $path, callable $callback)
    {
        static::$paths[$path] = $callback;
    }



    /**
     * get the name to use for the ajax action
     *
     * @return string
     */
    protected static function getActionName(): string
    {
        if ( ! static::$action) {
            static::$action = apply_filters('genie_ajax_action', Registry::get('genie_config', 'ajax_action'));
        }

        return static::$action;
    }

}
