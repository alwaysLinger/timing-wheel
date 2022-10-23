<?php

namespace Al\TimingWheel\Annotation;

use Al\TimingWheel\Exception\DelayInvokeTimeoutException;
use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Utils\Arr;

/**
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
class DelayInvoke extends AbstractAnnotation
{
    /**
     * Time that callable would be hanged before got invoked
     * And maximum delay time should be within one hour
     * @var int
     */
    public $delay;

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
        $delay = Arr::get($value, 'delay', 0);

        throw_unless(
            $delay >= 0 && $delay <= 3600 * 1000,
            DelayInvokeTimeoutException::class
        );

        return compact('delay');
    }

    public function getScheduleDueTime(): int
    {
        return $this->delay;
    }
}