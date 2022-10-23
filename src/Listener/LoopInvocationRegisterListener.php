<?php

declare(strict_types=1);

namespace Al\TimingWheel\Listener;

use Al\TimingWheel\Annotation\LoopInvoke;
use Al\TimingWheel\TimingWheelManager;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Utils\ApplicationContext;

/**
 * @Listener
 */
class LoopInvocationRegisterListener extends AbstractTimerRegister
{
    protected $annotation = LoopInvoke::class;

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    public function getInvokeMethodName(): string
    {
        return $this->container->get(ConfigInterface::class)->get('timing-wheel.loop_method_name', TimingWheelManager::Invoke_Method);
    }

    public function registerInvocation(int $ms, string $className, string $methodName)
    {
        $this->getServer()->tick(
            $ms,
            fn() => call([ApplicationContext::getContainer()->get($className), $methodName])
        );
    }
}
