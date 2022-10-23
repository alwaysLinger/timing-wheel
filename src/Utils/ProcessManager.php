<?php

namespace Al\TimingWheel\Utils;

use Al\TimingWheel\TimingWheelManager;
use Hyperf\Utils\Traits\Container;
use Swoole\Process;

class ProcessManager
{
    use Container;

    public static function setTimingManager(Process $process)
    {
        static::set(TimingWheelManager::Process_Name, $process);
    }

    public static function getProcess(): Process
    {
        return static::get(TimingWheelManager::Process_Name);
    }
}