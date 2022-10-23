<?php

namespace Al\TimingWheel\Event;

use Swoole\Process;

class AddAnnotationTask
{
    /**
     * @var Process
     */
    public $process;

    public function __construct(Process $process)
    {
        $this->process = $process;
    }
}