<?php

namespace GeniePress\Components;

use GeniePress\Genie;
use GeniePress\Registry;
use GeniePress\Utilities\AddShortcode;
use GeniePress\Utilities\HookInto;

/**
 * Class Session
 * PHP Session Handler
 *
 * @package GeniePress
 */
class Session
{

    /**
     * the name of the session
     * @var string
     */
    protected static $sessionName = '';



    /**
     * Setup
     */
    public static function setup($sessionName = '', $hook = 'after_setup_theme', $priority = 10): void
    {
        if ($sessionName) {
            static::$sessionName = $sessionName;
        }

        // Run once everything has been set up
        HookInto::action($hook, $priority)
            ->run(function () {
                $sessionName = static::getSessionName();

                session_name($sessionName);

                if ( ! session_id()) {
                    session_start();
                }

                $maxTime      = apply_filters(Genie::hookName('session_max_time'), ini_get("session.gc_maxlifetime"));
                $cookieDomain = apply_filters(Genie::hookName('session_cookie_domain'), COOKIE_DOMAIN);
                $secure       = apply_filters(Genie::hookName('session_secure'), true);
                $httpOnly     = apply_filters(Genie::hookName('session_http_only'), true);
                setcookie(session_name(), session_id(), time() + $maxTime, '/', $cookieDomain, $secure, $httpOnly);

                // Last request was more than $maxTime seconds ago?
                if (isset($_SESSION['sessionLastActivity']) && (time() - $_SESSION['sessionLastActivity'] > $maxTime)) {
                    static::destroy();
                }

                // Update last activity time stamp
                $_SESSION['sessionLastActivity'] = time();

                if ( ! isset($_SESSION['sessionCreated'])) {
                    $_SESSION['sessionCreated'] = time();
                } elseif (time() - $_SESSION['sessionCreated'] > $maxTime) {
                    // The Session started more than $maxTime seconds ago,
                    // change session ID for the current session and invalidate old session ID
                    session_regenerate_id(true);

                    // update creation time
                    $_SESSION['sessionCreated'] = time();
                }
            });

        // We process all variables here and also capture and query variables.
        HookInto::action('parse_request')
            ->run(function ($wp) {
                static::processVariables();
                static::set('query_vars', $wp->query_vars);
            });

        // Plug the session into all views
        HookInto::filter(Genie::hookName('view_variables'))
            ->run(function ($vars) {
                return array_merge($vars, ['_session' => $_SESSION]);
            });

        /**
         * Var shortcode
         * [var] shortcode
         * [var email default='']
         */
        AddShortcode::called('var')
            ->run(function ($attributes) {
                $a = (object) shortcode_atts([
                    'var'     => ! empty($attributes) ? $attributes[0] : '',
                    'default' => '',
                ], $attributes);

                return static::find($a->var, $a->default);
            });
    }



    /**
     * Destroys the session
     */
    public static function destroy(): void
    {
        // unset $_SESSION variable for the run-time
        session_unset();

        // destroy session data in storage
        session_destroy();
    }



    /**
     * Get a value from the session
     *
     * @param $var
     * @param  mixed  $default
     *
     * @return array|bool|mixed
     */
    public static function get($var, $default = false)
    {
        return self::find($var, $default);
    }



    /**
     * Get the session ID
     *
     * @return string
     */
    public static function getSessionID(): string
    {
        return session_id();
    }



    /**
     * Check if the session has a value
     *
     * @param $field
     *
     * @return bool
     */
    public static function has($field): bool
    {
        return (bool) self::find($field);
    }



    /**
     * Check if there is an active session
     *
     * @return bool
     */
    public static function isSessionActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }



    /**
     * Get the variables that needs to be saved, and then add them to the session.
     */
    public static function processVariables(): void
    {
        $fields = apply_filters(Genie::hookName('session_parse_request'), []);

        foreach ($fields as $field) {
            if (isset($_REQUEST[$field])) {
                if (function_exists('filter_var')) {
                    $val = filter_var($_REQUEST[$field], FILTER_SANITIZE_STRING);
                } else {
                    $val = $_REQUEST[$field];
                }
                $_SESSION[$field] = stripslashes($val);
            }
        }
    }



    /**
     * Remove a value for the session
     *
     * @param $var
     */
    public static function remove($var): void
    {
        unset($_SESSION[$var]);
    }



    /**
     * Save a value to the Session
     *
     * @param $var
     * @param $value
     */
    public static function set($var, $value): void
    {
        $_SESSION[$var] = $value;
    }



    /**
     * look for a value in the session. can be accessed by dot notation (like twig)
     * $object->property['index']
     * Session::get(object.property.index);
     *
     * @param $var
     * @param  mixed  $default
     *
     * @return mixed
     */
    protected static function find($var, $default = false)
    {
        if ( ! self::isSessionActive()) {
            return $default;
        }

        $lookAt = $_SESSION;
        $keys   = explode('.', $var);
        foreach ($keys as $key) {
            if (is_object($lookAt) && property_exists($lookAt, $key)) {
                $lookAt = $lookAt->$key;
                continue;
            }
            if (is_array($lookAt) && isset($lookAt[$key])) {
                $lookAt = $lookAt[$key];
                continue;
            }
            $lookAt = $default;
        }

        return $lookAt;
    }



    /**
     * get the name to use for the ajax action
     *
     * @return string
     */
    protected static function getSessionName(): string
    {
        if ( ! static::$sessionName) {
            static::$sessionName = Registry::get('genie_config', 'session_name', 'genie_session');
        }

        return apply_filters(Genie::hookName('session_name'), static::$sessionName);
    }

}
