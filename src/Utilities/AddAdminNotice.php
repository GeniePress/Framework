<?php

namespace GeniePress\Utilities;

class AddAdminNotice

{

    /**
     * the type of the admin notice
     *
     * @var string error|warning|success|info
     */
    protected $type = 'success';

    /**
     * Is this admin notice dismissible ?
     *
     * @var bool
     */
    protected $dismissible = true;

    /**
     * The message
     *
     * @var string
     */
    protected $message = '';



    /**
     * constructor.
     *
     * @param  string  $type
     */
    public function __construct(string $type)
    {
        $this->type = strtolower($type);
    }



    /**
     * static constructor
     *
     * @param  string  $message
     *
     * @return static
     */
    public static function error(string $message): AddAdminNotice
    {
        $notice          = new static('error');
        $notice->message = $message;

        return $notice;
    }



    /**
     * static constructor
     *
     * @param  string  $message
     *
     * @return static
     */
    public static function info(string $message): AddAdminNotice
    {
        $notice          = new static('info');
        $notice->message = $message;

        return $notice;
    }



    /**
     * static constructor
     *
     * @param  string  $message
     *
     * @return static
     */
    public static function success(string $message): AddAdminNotice
    {
        $notice          = new static('success');
        $notice->message = $message;

        return $notice;
    }



    /**
     * static constructor
     *
     * @param  string  $message
     *
     * @return static
     */
    public static function warning(string $message): AddAdminNotice
    {
        $notice          = new static('warning');
        $notice->message = $message;

        return $notice;
    }



    /**
     *
     */
    public function display(): void
    {
        HookInto::action('admin_notices')
            ->run(function () {
                echo $this->render();
            });
    }



    /**
     * This message can be dismissed
     *
     * @return $this
     */
    public function isDismissible(): AddAdminNotice
    {
        $this->dismissible = true;

        return $this;
    }



    /**
     * This message can NOT be dismissed
     *
     * @return $this
     */
    public function isNotDismissible(): AddAdminNotice
    {
        $this->dismissible = false;

        return $this;
    }



    /**
     * Render the HTML required to show the message
     *
     * @return string
     */
    public function render(): string
    {
        $dismissible = $this->dismissible ? 'is-dismissible' : '';

        return "<div class='notice notice-$this->type $dismissible'><p><strong>$this->message</strong></p></div>";
    }

}
