<?php

namespace Al\TimingWheel\Utils;

use Al\TimingWheel\Contract\PackerInterface;
use Al\TimingWheel\Exception\NoTimingWheelProcessException;
use Hyperf\Utils\ApplicationContext;
use Swoole\Process;

class TaskEmitter
{
    public static function sendTask(array $tasks): bool
    {
        /** @var Process $manager */
        $manager = tap(
            ProcessManager::getProcess(),
            fn($process) => throw_unless($process, NoTimingWheelProcessException::class)
        );

        $packer = ApplicationContext::getContainer()->get(PackerInterface::class);
        $messages = $packer->pack($tasks);

        return (bool)$manager->exportSocket()->send($messages, 5);
    }
}