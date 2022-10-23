<?php

declare(strict_types=1);

namespace Al\TimingWheel\Listener;

use Al\TimingWheel\Annotation\DelayInvoke;
use Al\TimingWheel\TimingWheelManager;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Utils\ApplicationContext;

/**
 * @Listener
 */
class DelayInvocationRegisterListener extends AbstractTimerRegister
{
    protected $annotation = DelayInvoke::class;

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    public function getInvokeMethodName(): string
    {
        return $this->container->get(ConfigInterface::class)->get('timing-wheel.delay_method_name', TimingWheelManager::Invoke_Method);
    }

    public function registerInvocation(int $ms, string $className, string $methodName)
    {
        if ($ms == 0) {
            wait(fn() => $this->getServer()->defer(fn() => call([ApplicationContext::getContainer()->get($className), $methodName])));
        } else {
            $this->getServer()->after(
                $ms,
                fn() => call([ApplicationContext::getContainer()->get($className), $methodName])
            );
        }
    }
}
