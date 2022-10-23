<?php

namespace Al\TimingWheel;

use Al\TimingWheel\Exception\NonCallableActionException;
use Al\TimingWheel\Exception\NonDelayTimeReceivedException;
use Al\TimingWheel\Exception\NonTaskNameException;
use Hyperf\Utils\Arr;

class Task
{
    public const AddTask_Operation = 1;
    public const DelTask_Operation = 2;

    /**
     * @var int
     */
    private $slotNum;

    /**
     * @var int
     */
    private $currentSlot;

    /**
     * @var int
     */
    private $terms;

    /**
     * Add or delete task
     * @var int
     */
    private $operation;

    /**
     * The task from user
     * @var array
     */
    private $payload;

    public function __construct(array $task, int $slotNum, int $currentSlot, int $terms)
    {
        $this->slotNum = $slotNum;
        $this->currentSlot = $currentSlot;
        $this->terms = $terms;
        $this->formatTask($task);
    }

    public function formatTask(array $task): Task
    {
        /**
         * @var int $operate
         * @var array $payload ['name', 'delay', 'action', 'context']
         */
        extract($task);

        return $operate == static::AddTask_Operation ? $this->formatAddTask($payload) : $this->formatDelTask($payload);
    }

    protected function formatAddTask(array $payload)
    {
        $payload['name'] = tap(
            Arr::get($payload, 'name'),
            fn($name) => throw_unless(
                $name,
                NonTaskNameException::class
            )
        );

        $payload['delay'] = tap(
            Arr::get($payload, 'delay'),
            fn($delay) => throw_unless(
                $delay && is_integer($delay) && $delay > 0,
                NonDelayTimeReceivedException::class
            )
        );

        [$payload['terms'], $payload['slot']] = $this->termAndSlot($payload['delay']);

        $payload['action'] = tap(
            Arr::get($payload, 'action'),
            fn($action) => throw_unless(
                is_callable($action, true),
                NonCallableActionException::class
            )
        );

        $payload['context'] = $payload['context'] ?? [];

        $this->payload = $payload;
        $this->operation = static::AddTask_Operation;

        return $this;
    }

    private function termAndSlot(int $delay): array
    {
        return value(
            function ($slot) use ($delay) {
                $slot = $slot == $this->slotNum ? 0 : $slot;
                $terms = floor($delay / $this->slotNum) + $this->terms + ($slot == 0 ? 1 : 0);

                return [$terms, $slot];
            },
            $delay % $this->slotNum + $this->currentSlot
        );
    }

    protected function formatDelTask(array $payload)
    {
        $payload['name'] = tap(
            Arr::get($payload, 'name'),
            fn($name) => throw_unless(
                $name,
                NonTaskNameException::class
            )
        );
        $this->payload = $payload;
        $this->operation = static::DelTask_Operation;

        return $this;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function needsAdd(): bool
    {
        return $this->operation == static::AddTask_Operation;
    }

    public function needsDel(): bool
    {
        return !$this->needsAdd();
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->payload)) {
            return $this->payload[$name];
        }
    }

    public function run()
    {
        call_user_func_array($this->action, $this->getContext($this->context));
    }

    protected function getContext(array $context)
    {
        return parallel(
            array_map(
                fn($argument) => fn() => is_callable($argument, true) ? call_user_func($argument) : $argument,
                $context
            )
        );
    }
}