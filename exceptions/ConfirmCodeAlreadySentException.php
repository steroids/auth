<?php


namespace steroids\auth\exceptions;


use Yii;
use yii\base\Exception;

class ConfirmCodeAlreadySentException extends Exception
{
    /**
     * Constructor.
     * @param string $message error message
     * @param int $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(425, $message, $code, $previous);
    }
}