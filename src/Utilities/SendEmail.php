<?php

namespace GeniePress\Utilities;

use InlineStyle\InlineStyle;

/**
 * Class SendEmail
 * A Handy wrapper around wp_mail (https://developer.wordpress.org/reference/functions/wp_mail/)
 * $body = View::make('Emails/example.twig',['name' => 'Sunil]);
 * SendEmail::to('someone@somewhere.com')
 *   ->from('from@someone.com')
 *   ->body($body)
 *   ->subject('test email')
 *   ->send();
 *
 * @package GeniePress
 */
class SendEmail
{

    /**
     * To email address
     *
     * @var array|string
     */
    protected $email;

    /**
     * The name of the person receiving the email
     *
     * @var string
     */
    protected $name;

    /**
     * The subject
     *
     * @var string
     */
    protected $subject;

    /**
     * Additional headers to send with the email
     *
     * @protected string[]
     */
    protected $headers = [
        'Content-Type: text/html; charset=UTF-8',
    ];

    /**
     * The email body (HTML)
     *
     * @var string
     */
    protected $body = '';

    /**
     * A list of attachments
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * Should CSS Styles be inlined?
     *
     * @var bool
     */
    protected $inlineStyles = true;



    /**
     * SendEmail constructor.
     *
     * @param  string  $email
     * @param  string  $name
     */
    public function __construct(string $email, string $name = '')
    {
        $this->email($email);
        $this->name($name);
    }



    /**
     * Static constructor
     * SendEmail::to('someonbe@somedomains.com')
     * ->body('...')
     * ->subject('....')
     * ->send()
     *
     * @param  string|array  $email
     * @param  string  $name
     *
     * @return static
     */
    public static function to($email, string $name = ''): SendEmail
    {
        return new static($email, $name = '');
    }



    /**
     * Add an attachment to the email message
     *
     * @param $file
     *
     * @return $this
     */
    public function addAttachment($file): SendEmail
    {
        $this->attachments[] = $file;

        return $this;
    }



    /**
     * Adds a header to the email
     *
     * @param  string  $header
     */
    function addHeader(string $header)
    {
        $this->headers[] = $header;
    }



    /**
     * Sets the body of the email
     *
     * @param  string  $body
     *
     * @return $this
     */
    function body(string $body): SendEmail
    {
        $this->body = $body;

        return $this;
    }



    /**
     * Sets the email of the recipient
     *
     * @param $email
     *
     * @return $this
     */
    function email($email): SendEmail
    {
        $this->email = $email;

        return $this;
    }



    /**
     * Inline Styles from the email
     *
     * @return string
     */
    function format(): string
    {
        if ( ! $this->inlineStyles) {
            return $this->body;
        }

        $htmlDoc = new InlineStyle($this->body);
        $htmlDoc->applyStylesheet($htmlDoc->extractStylesheets());

        return $htmlDoc->getHTML();
    }



    /**
     * Sets the sender of the email
     *
     * @param  string  $email
     * @param  string  $name
     *
     * @return $this
     */
    function from(string $email, string $name = ''): SendEmail
    {
        $this->addHeader("From: $name <{$email}>");

        return $this;
    }



    /**
     * Run the inline styler?
     *
     * @param  bool  $bool
     *
     * @return $this
     */
    function inlineStyles(bool $bool): SendEmail
    {
        $this->inlineStyles = $bool;

        return $this;
    }



    /**
     * Sets the name of the email recipient
     *
     * @param $name
     *
     * @return $this
     */
    function name($name): SendEmail
    {
        $this->name = $name;

        return $this;
    }



    /**
     * Send the email using wp_mail
     *
     * @return bool
     */
    function send(): bool
    {
        return wp_mail($this->email, $this->subject, $this->format(), $this->headers, $this->attachments);
    }



    /**
     * Sets the subject of the email
     *
     * @param  string  $subject
     *
     * @return $this
     */
    function subject(string $subject): SendEmail
    {
        $this->subject = $subject;

        return $this;
    }

}
