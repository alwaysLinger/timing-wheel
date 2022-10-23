<?php

namespace Al\TimingWheel\Contract;

interface TimedInvocationInterface
{
    public function getInvokeMethodName(): string;

    public function registerInvocation(int $ms, string $className, string $methodName);
}