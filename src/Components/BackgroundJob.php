<?php
/**
 * @noinspection PhpExpressionResultUnusedInspection
 */

namespace GeniePress\Components;

use GeniePress\Genie;
use GeniePress\Registry;
use GeniePress\Request;
use GeniePress\Utilities\HookInto;
use Throwable;

/**
 * Class BackgroundJob
 *
 *
 * BackgroundJob::start('A description')
 *   ->add( 'hook_name', $arg1, $arg2  )
 *   ->add( 'hook_name', $arg1, $arg2  )
 *   ->send();
 */
class BackgroundJob
{

    /**
     * The post ID job we're currently processing
     * Once set this turns off all other triggers.
     *
     * @var int
     */
    protected static $processingId = false;

    /**
     * The variable name
     *
     * @var string
     */
    protected static $variableName = '';

    /**
     * name of the background job
     *
     * @var string
     */
    protected $name;

    /**
     * Array of function calls to perform on this background Job.
     *
     * @var array
     */
    protected $hooks = [];

    /**
     * Should invalid SSL be ignored?
     *
     * @var bool
     */
    protected $ignoreSSL = false;



    /**
     * Setup
     *
     * @param  string  $variableName
     */
    public static function setup(string $variableName = ''): void
    {
        if ($variableName) {
            static::$variableName = $variableName;
        }

        //  Check if we're processing a background Job
        $variableName = static::getVariableName();

        if ( ! Request::has($variableName)) {
            return;
        }

        $id = absint(Request::get($variableName));
        if ( ! $id) {
            return;
        }

        // This clever bit of code ends the connection, so we don't hold up the user.
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

                try {
                    $hooks = json_decode(base64_decode($job->post_content), true, 512, JSON_THROW_ON_ERROR);
                    foreach ($hooks as $args) {
                        $hook = array_shift($args);
                        do_action_ref_array($hook, $args);
                    }
                } catch (Throwable $exception) {
                    do_action(Genie::hookName('background_job_error'), $exception, $job);
                }

                wp_delete_post(static::$processingId, true);
                exit;
            });
    }



    /**
     * Constructor
     *
     * @param  string  $name
     */
    public function __construct(string $name = '')
    {
        $this->name = $name;
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
     * @param  string  $name
     *
     * @return BackgroundJob
     */
    public static function start(string $name = ''): BackgroundJob
    {
        return new static($name);
    }



    /**
     * Add a hook to the call Stack
     *
     * @return $this
     */
    public function add(): BackgroundJob
    {
        $this->hooks[] = func_get_args();

        return $this;
    }



    /**
     * Should we ignore SSL ?
     *
     * @return $this
     */
    public function ignoreSSL(): BackgroundJob
    {
        $this->ignoreSSL = true;

        return $this;
    }



    /**
     * Save the job and send it for processing.
     */
    public function send(): void
    {
        //Save the job for processing
        $id = wp_insert_post([
            'post_type'    => 'genie_background_job',
            'post_title'   => $this->name,
            'post_content' => base64_encode(json_encode($this->hooks)),
        ]);

        $variableName = static::getVariableName();

        $url = home_url()."/?$variableName=$id";

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



    /**
     * get the variable name used for the background job
     * @return string
     */
    protected static function getVariableName(): string
    {
        if ( ! static::$variableName) {
            static::$variableName = Registry::get('genie_config', 'bj_id', 'genie_bj_id');
        }

        return apply_filters(Genie::hookName('bj_id'), static::$variableName);
    }

}
