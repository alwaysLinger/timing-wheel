<?php

namespace Al\TimingWheel\Annotation;

use Al\TimingWheel\Exception\LoopIntervalException;
use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Utils\Arr;

/**
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class LoopInvoke extends AbstractAnnotation
{
    /**
     * The invocation interval
     * And maximum interval time should be within one hour
     * @var int
     */
    public $interval;

    public function __construct(...$value)
    {
        $value = $this->formatParams($value);

        foreach ($value as $key => $val) {
            if (property_exists($this, $key)) {
                $this->{$key} = $val;
            }
        }
    }

    protected function formatParams($value): array
    {
        $value = $value[0];
        $interval = tap(
            Arr::get($value, 'interval', 0),
            fn($interval) => throw_unless(
                $interval && $interval > 0 && $interval <= 3600 * 1000,
                LoopIntervalException::class
            )
        );

        return compact('interval');
    }

    public function getScheduleDueTime(): int
    {
        return $this->interval;
    }
}