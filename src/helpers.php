<?php

use Al\TimingWheel\Utils\TaskEmitter;

if (!function_exists("throw_if")) {
    function throw_if($boolean, $exception, $message = '')
    {
        if ($boolean) {
            throw (is_string($exception) ? new $exception($message) : $exception);
        }
    }
}

if (!function_exists('throw_unless')) {
    function throw_unless($boolean, $exception, $message = '')
    {
        if (!$boolean) {
            throw (is_string($exception) ? new $exception($message) : $exception);
        }
    }
}

if (!function_exists('send_tasks')) {
    function send_tasks(array $tasks): bool
    {
        return TaskEmitter::sendTask($tasks);
    }
}
