<?php

namespace Al\TimingWheel\Exception;

class ProcessNotFoundException extends \RuntimeException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: 'Timing-wheel manager process is not running', $code, $previous);
    }
}