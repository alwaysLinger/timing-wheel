<?php

namespace Al\TimingWheel\Exception;

class NonDelayTimeReceivedException extends \InvalidArgumentException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: 'Timing-wheel tasks require a significant delay time', $code, $previous);
    }
}