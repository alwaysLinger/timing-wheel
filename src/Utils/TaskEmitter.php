<?php

namespace Al\TimingWheel\Utils;

use Al\TimingWheel\Contract\PackerInterface;
use Hyperf\Utils\ApplicationContext;

class TaskEmitter
{
    public static $sendTimeout = 5.0;

    public static function setSendTimeout(float $timeout)
    {
        static::$sendTimeout = $timeout;
    }

    public static function sendTask($tasks): bool
    {
        $tasks = optional($tasks)->toArray() ?: $tasks;

        $packer = ApplicationContext::getContainer()->get(PackerInterface::class);
        $messages = $packer->pack($tasks);

        return (bool)ProcessManager::getProcess()->exportSocket()->send($messages, static::$sendTimeout);
    }
}