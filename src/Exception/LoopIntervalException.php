<?php

namespace Al\TimingWheel\Exception;

class LoopIntervalException extends \InvalidArgumentException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: 'Invocation interval time should between 1 - 3600 seconds', $code, $previous);
    }
}