<?php

namespace Al\TimingWheel\Utils;

use Al\TimingWheel\Exception\ArrayAccessibleException;
use Hyperf\Utils\Arr;

abstract class CircularArray extends \SplFixedArray
{
    abstract public function beforeCurrent();

    abstract public function beforeNewTerm();

    public function next()
    {
        if ($this->shouldNewTerm()) {
            $this->rewind();

            return;
        }

        parent::next();
    }

    public function current()
    {
        $this->beforeCurrent();

        return parent::current();
    }

    protected function shouldNewTerm(): bool
    {
        return tap(
            $this->key() + 1 == $this->getSize(),
            fn($shouldRewind) => $shouldRewind && $this->beforeNewTerm()
        );
    }


    /**
     * parent fromArray method return a SplFixedArray instance, so have to override this method to return a CircularArray instance
     * @param $array
     * @param bool $preserveKeys
     * @return CircularArray
     */
    public static function fromArray($array, $preserveKeys = true): CircularArray
    {
        throw_unless(Arr::accessible($array), ArrayAccessibleException::class);

        return tap(
            new static(count($array)),
            function ($instance) use ($array) {
                foreach ($array as $key => $item) {
                    $instance[$key] = $item;
                }
            }
        );
    }
}