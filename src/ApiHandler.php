<?php

namespace GeniePress;

use GeniePress\Interfaces\GenieComponent;
use GeniePress\Utilities\HookInto;
use Throwable;
use Twig\Environment;
use Twig\TwigFunction;

/**
 * Class ApiHandler
 *
 * @package GeniePress
 */
class ApiHandler implements GenieComponent
{

    /**
     * An array of paths to use for ajax calls
     *
     * @var array
     */
    protected static $routes = [];



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
                $path   = static::getPath();
                $action = apply_filters(Genie::hookName('api_action'), Registry::get('genie_config', 'api_action'));

                add_rewrite_rule($path.'/(.*)$', 'wp-admin/admin-ajax.php?action='.$action.'&route=$1', 'top');

                HookInto::action('wp_ajax_'.$action)
                    ->orAction('wp_ajax_nopriv_'.$action)
                    ->run(function () use ($action) {
                        Request::maybeParseInput();

                        do_action(Genie::hookName('received_api_request'), $action, Request::getData());

                        $route = Request::get('route');

                        if ( ! $route) {
                            Response::error([
                                'message' => "No request specified",
                            ]);
                        }

                        if ( ! static::canHandle($route)) {
                            Response::notFound([
                                'message' => "Request: $route not found",
                            ]);
                        }

                        // The Callback exists
                        $callback = static::$routes[$route]->callback;
                        $method   = static::$routes[$route]->method;

                        if ($method !== Request::method()) {
                            Response::error([
                                'message' => "This route does not support ".Request::method()." requests",
                            ]);
                        }

                        $params = Tools::getCallableParameters($callback);

                        if (is_wp_error($params)) {
                            Response::error([
                                'message' => $params->get_error_message(),
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
                                    Response::failure(['message' => "required parameter $name is missing"]);
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
         * Create the api_url function in twig that can prefix the right path, and add the nonce.
         */
        HookInto::filter(Genie::hookName('view_twig'))
            ->run(function (Environment $twig) {
                $function = new TwigFunction('api_url', [static::class, 'generateUrl']);
                $twig->addFunction($function);

                return $twig;
            });
    }



    /**
     * Check that a path is registered
     *
     * @param $route
     *
     * @return bool
     */
    public static function canHandle($route): bool
    {
        return array_key_exists($route, static::$routes);
    }



    /**
     * Generate a url for an api call with the $requestPath
     *
     * @param $route
     *
     * @return string
     */
    public static function generateUrl($route): string
    {
        return home_url(static::getPath().'/'.$route);
    }



    /**
     * Register an ajax callback function
     *
     * @param  string  $route
     * @param  string  $method
     * @param  callable  $callback
     */
    public static function register(string $route, string $method, callable $callback): void
    {
        static::$routes[$route] = (object) [
            'method'   => strtoupper(trim($method)),
            'callback' => $callback,
        ];
    }



    /**
     * get the api Path
     *
     * @return string
     */
    protected static function getPath(): string
    {
        return apply_filters(Genie::hookName('api_path'), Registry::get('genie_config', 'api_path'));
    }

}
