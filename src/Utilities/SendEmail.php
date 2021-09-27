<?php

namespace GeniePress\Utilities;

use InlineStyle\InlineStyle;

/**
 * Class SendEmail
 * A Handy wrapper around wp_mail (https://developer.WordPress.org/reference/functions/wp_mail/)
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
     */
    public function __construct(string $email)
    {
        $this->email($email);
    }



    /**
     * Static constructor
     * SendEmail::to('Someone <someonbe@somedomains.com>')
     * ->body('...')
     * ->subject('....')
     * ->send()
     *
     * @param  string|array  $email
     *
     * @return static
     */
    public static function to($email): SendEmail
    {
        return new static($email);
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
    public function addHeader(string $header): void
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
    public function body(string $body): SendEmail
    {
        $this->body = $body;

        return $this;
    }



    /**
     * Sets the email of the recipient
     *
     * @param  string|array  $email
     *
     * @return $this
     */
    public function email($email): SendEmail
    {
        $this->email = $email;

        return $this;
    }



    /**
     * Sets the sender of the email
     *
     * @param  string  $email
     * @param  string  $name
     *
     * @return $this
     */
    public function from(string $email, string $name = ''): SendEmail
    {
        $from = $name ? "$name <$email>" : $email;
        $this->addHeader("From: $from");

        return $this;
    }



    /**
     * Run the inline styler?
     *
     * @param  bool  $bool
     *
     * @return $this
     */
    public function inlineStyles(bool $bool): SendEmail
    {
        $this->inlineStyles = $bool;

        return $this;
    }



    /**
     * Send the email using wp_mail
     *
     * @return bool
     */
    public function send(): bool
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
    public function subject(string $subject): SendEmail
    {
        $this->subject = $subject;

        return $this;
    }



    /**
     * Inline Styles from the email
     *
     * @return string
     */
    protected function format(): string
    {
        if ( ! $this->inlineStyles) {
            return $this->body;
        }

        $internalErrors = libxml_use_internal_errors(true);

        $htmlDoc     = new InlineStyle($this->body);
        $styleSheets = $htmlDoc->extractStylesheets();
        foreach ($styleSheets as $styleSheet) {
            $htmlDoc->applyStylesheet($styleSheet);
        }
        libxml_use_internal_errors($internalErrors);

        return $htmlDoc->getHTML();
    }

}
