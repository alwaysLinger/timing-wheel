<?php

namespace Al\TimingWheel\Utils;

use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Throwable;

class TimingWheelExceptionFormatter implements FormatterInterface
{
    public function format(Throwable $throwable): string
    {
        return sprintf(
            "Previous:%s: %s(%s) in %s:%s\nPrevious stack trace:%s\n %s: %s(%s) in %s:%s\nStack trace:\n%s",
            $throwable->getPrevious() ? get_class($throwable->getPrevious()) : 'no previous throwable',
            optional($throwable->getPrevious())->getMessage() ?: 'no previous throwable',
            optional($throwable->getPrevious())->getCode() ?: 0,
            optional($throwable->getPrevious())->getFile() ?: 'no previous throwable',
            optional($throwable->getPrevious())->getLine() ?: 0,
            optional($throwable->getPrevious())->getTraceAsString() ?: 'no previous throwable',
            get_class($throwable),
            $throwable->getMessage(),
            $throwable->getCode(),
            $throwable->getFile(),
            $throwable->getLine(),
            $throwable->getTraceAsString()
        );
    }
}