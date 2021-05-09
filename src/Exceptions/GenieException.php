<?php

namespace GeniePress\Exceptions;

use Exception;
use GeniePress\Traits\HasData;
use JsonSerializable;
use Throwable;

/**
 * Class GenieException
 *
 * @package GeniePress\Exceptions
 * @property $attributes
 * @property $backtrace
 */
class GenieException extends Exception implements JsonSerializable
{

    use HasData;

    /**
     * GenieException constructor.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  Throwable|null  $previous
     */
    function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        do_action('genie_exception', $this);
    }



    /**
     * Static constructor
     *
     * @param $message
     *
     * @return static
     */
    public static function withMessage($message): GenieException
    {
        return new static($message);
    }



    /**
     * Return the exception as a json object
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'message' => $this->getMessage(),
            'code'    => $this->getCode(),
            'data'    => $this->getData(),
        ];
    }



    /**
     * Set the code for this Exception
     *
     * @param $code
     *
     * @return $this
     */
    public function withCode($code): GenieException
    {
        $this->code = $code;

        return $this;
    }



    /**
     * Add data to this exception
     *
     * @param $data
     *
     * @return $this
     */
    public function withData($data): GenieException
    {
        $this->data = $data;

        return $this;
    }
}
