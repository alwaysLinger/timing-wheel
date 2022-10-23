<?php

namespace Al\TimingWheel;

use Al\TimingWheel\Utils\CircularArray;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Psr\Container\ContainerInterface;
use Swoole\Timer;

class TaskTimingWheel extends CircularArray
{
    /**
     * @var int
     */
    private $slotNum;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Times that this wheel got completely iterated
     * @var int
     */
    protected $terms = 1;

    /**
     * @var array
     */
    protected $delList = [];

    public function __construct(int $size, ContainerInterface $container)
    {
        $this->slotNum = $size;
        $this->container = $container;
        $this->timer = Timer::tick(1 * 1000, fn() => $this->runTasks());

        parent::__construct($size);
    }

    public function beforeNewTerm()
    {
        $this->terms++;
    }

    public function handleTask(array $task)
    {
        $task = new Task($task, $this->slotNum, $this->key(), $this->terms);
        if ($task->needsAdd()) {
            $this->addTask($task);
        } else {
            $this->delTask($task->name);
        }
    }

    private function addTask(Task $task)
    {
        $taskList = value(
            function ($taskList) use ($task) {
                if (is_null($taskList)) {
                    return [$task->name => $task];
                } else {
                    $taskList[$task->name] = $task;
                    return $taskList;
                }
            },
            $this[$task->slot]
        );

        $this->setSlot($taskList, $task->slot);
    }

    private function delTask(string $name)
    {
        $this->delList[$name] = $name;
    }

    public function beforeCurrent()
    {
        $this->currentSlot = $this->key();
    }

    private function runTasks()
    {
        $taskList = $this->current() ?: [];
        foreach ($taskList as $name => $task) {
            $this->runTask($taskList, $name, $task);
        }

        $this->next();
    }

    private function runTask(array $taskList, string $name, Task $task)
    {
        if ($this->shouldRun($taskList, $name, $task)) {
            go(function () use ($taskList, $name, $task) {
                try {
                    $task->run();
                } catch (\Throwable $th) {
                    $formatter = $this->container->get(FormatterInterface::class);
                    $this->container->get(StdoutLoggerInterface::class)->error($formatter->format($th));
                } finally {
                    unset($taskList[$name]);
                    $this->setSlot($taskList);
                }
            });
        }
    }

    protected function shouldRun(array $taskList, string $name, Task $task): bool
    {
        return $this->doDelTask($taskList, $name) && $this->taskTermDue($task);
    }

    private function setSlot(array $taskList, ?int $slot = null)
    {
        $this[$slot ?? $this->key()] = $taskList;
    }

    private function doDelTask(array $taskList, string $name): bool
    {
        return tap(
            !in_array($name, $this->delList),
            function ($reserved) use ($taskList, $name) {
                if (!$reserved) {
                    unset($this->delList[$name]);
                    unset($taskList[$name]);
                    $this->setSlot($taskList);
                }
            }
        );
    }

    protected function taskTermDue(Task $task): bool
    {
        return $this->terms == $task->terms;
    }
}