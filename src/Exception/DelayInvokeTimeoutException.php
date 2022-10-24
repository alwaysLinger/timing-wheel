<?php

namespace Al\TimingWheel\Exception;

class DelayInvokeTimeoutException extends \InvalidArgumentException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: 'Invocation delay time should between 0 - 3600 seconds', $code, $previous);
    }
}