<?php

namespace GeniePress\Utilities;

use GeniePress\Tools;

class HookInto
{

    /**
     * An array of hooks and sequences
     *
     * @var array
     */
    protected $actions = [];

    /**
     * An array of filters and sequences
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The callback
     *
     * @var string
     */
    protected $callback = '';



    /**
     * constructor.
     *
     * @param  string  $hook
     * @param  int  $priority
     * @param  string  $type
     */
    public function __construct(string $hook, int $priority = 10, string $type = 'action')
    {
        $this->add($hook, $priority, $type);
    }



    /**
     * Static constructor
     *
     * @param  string  $action
     * @param  int  $priority
     *
     * @return static
     */

    public static function action(string $action, int $priority = 10): HookInto
    {
        return new static($action, $priority, 'action');
    }



    /**
     * Static constructor
     *
     * @param  string  $filter
     * @param  int  $priority
     *
     * @return static
     */

    public static function filter(string $filter, int $priority = 10): HookInto
    {
        return new static($filter, $priority, 'filter');
    }



    /**
     * Allow multiple hooks for the same action
     *
     * @param  string  $hook
     * @param  int  $priority
     *
     * @return $this
     */
    public function orAction(string $hook, int $priority = 10): HookInto
    {
        $this->add($hook, $priority, 'action');

        return $this;
    }



    /**
     * Allow multiple hooks for the same action
     *
     * @param  string  $hook
     * @param  int  $priority
     *
     * @return $this
     */
    public function orFilter(string $hook, int $priority = 10): HookInto
    {
        $this->add($hook, $priority, 'filter');

        return $this;
    }



    /**
     * __return_false
     */
    public function returnFalse(): void
    {
        $this->callback = '__return_false';
        $this->register();
    }



    /**
     * __return_true
     */
    public function returnTrue(): void
    {
        $this->callback = '__return_true';
        $this->register();
    }



    /**
     * Set the callback and register the actions and filters
     *
     * @param  callable  $callback
     */
    public function run(callable $callback): void
    {
        $this->callback = $callback;
        $this->register();
    }



    /**
     * Add a hook onto our $hooks array
     *
     * @param  string  $hook
     * @param  int  $sequence
     * @param  string  $type
     */
    protected function add(string $hook, int $sequence, string $type): void
    {
        if ($type === 'action') {
            $this->actions[$hook] = $sequence;
        } else {
            $this->filters[$hook] = $sequence;
        }
    }



    /**
     * Add the action or filter
     */
    protected function register(): void
    {
        $vars = Tools::getCallableParameters($this->callback);

        foreach ($this->actions as $hook => $priority) {
            add_action($hook, $this->callback, $priority, count($vars));
        }

        foreach ($this->filters as $hook => $priority) {
            add_filter($hook, $this->callback, $priority, count($vars));
        }
    }

}
