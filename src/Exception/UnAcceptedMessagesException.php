<?php

namespace Al\TimingWheel\Exception;

class UnAcceptedMessagesException extends \InvalidArgumentException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: 'Invalid messages argument for a packer', $code, $previous);
    }
}