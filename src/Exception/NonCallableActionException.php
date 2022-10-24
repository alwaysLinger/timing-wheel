<?php

namespace Al\TimingWheel\Exception;

class NonCallableActionException extends \InvalidArgumentException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: 'Timing-wheel tasks action must be callable', $code, $previous);
    }
}