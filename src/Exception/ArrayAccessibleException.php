<?php

namespace Al\TimingWheel\Exception;

class ArrayAccessibleException extends \InvalidArgumentException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?: '$array is not array accessible', $code, $previous);
    }
}