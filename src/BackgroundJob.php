<?php

namespace GeniePress;

use GeniePress\Interfaces\GenieComponent;
use GeniePress\Utilities\HookInto;
use Throwable;

/**
 * Class BackgroundJob
 *
 *
 * BackgroundJob::start()
 *  ->add( [Object::class , 'method'], [ 'param1' => $x, 'param2' => $y ] )
 *  ->send();
 */
class BackgroundJob implements GenieComponent
{

    /**
     * The post ID job we're currently processing
     * Once set this turns off all other triggers.
     *
     * @var int
     */
    static $processingId = false;

    /**
     * Array of function calls to perform on this background Job.
     *
     * @var array
     */
    var $calls = [];

    /**
     * Should invalid SSL be ignored?
     *
     * @var bool
     */
    protected $ignoreSSL = false;



    /**
     * Setup
     */
    public static function setup()
    {
        //  Check if we're processing a background Job
        $variableName = apply_filters('genie_bj_id', Registry::get('genie_bj_id'));

        if ( ! Request::has($variableName)) {
            return;
        }

        $id = absint(Request::get($variableName));
        if ( ! $id) {
            return;
        }

        // This clever bit of code ends the connection so we don't hold up the user.
        ob_end_clean();
        ignore_user_abort(true);
        ob_start();
        header("Connection: close");
        header("Content-Length: ".ob_get_length());
        ob_end_flush();
        flush();

        set_time_limit(0);

        // Stash the id we need to process in the init hook
        static::$processingId = $id;

        // Run the background as the last init job
        HookInto::action('init', 10000)
            ->run(function () {
                // Do we have a job to process ?

                $job = get_post(static::$processingId);
                if ( ! $job) {
                    exit;
                }

                $calls = unserialize(base64_decode($job->post_content));
                foreach ($calls as $args) {
                    $callback = array_shift($args);
                    try {
                        call_user_func_array($callback, $args);
                    } catch (Throwable $exception) {
                        do_action('genie_background_job_error', $exception, $callback, $args, $calls);
                        break;
                    }
                }
                wp_delete_post(static::$processingId, true);
                exit;
            });
    }



    /**
     * Check to see if this request is processing a background Job
     *
     * @return bool
     */
    public static function doingBackgroundJob(): bool
    {
        return (bool) static::$processingId;
    }



    /**
     * static constructor. Start a new BackgroundJob Call Stack
     *
     * @return BackgroundJob
     */
    public static function start(): BackgroundJob
    {
        return new static();
    }



    /**
     * Add a job to the call Stack
     *
     * @return $this
     */
    function add(): BackgroundJob
    {
        $this->calls[] = func_get_args();

        return $this;
    }



    /**
     * Should we ignore SSL ?
     *
     * @return $this
     */
    function ignoreSSL(): BackgroundJob
    {
        $this->ignoreSSL = true;

        return $this;
    }



    /**
     * Save the job and send it for processing.
     */
    function send()
    {
        //Save the job for processing
        $id = wp_insert_post([
            'post_type'    => 'genie_background_job',
            'post_content' => base64_encode(serialize($this->calls)),
        ]);

        $variableName = apply_filters('genie_bj_id', Registry::get('genie_bj_id'));

        $url = home_url()."/?{$variableName}={$id}";

        if ($this->ignoreSSL) {
            $context = stream_context_set_default([
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);
            get_headers($url, 0, $context);
        } else {
            get_headers($url);
        }
    }

}
