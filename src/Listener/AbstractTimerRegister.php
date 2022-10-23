<?php

namespace Al\TimingWheel\Listener;

use Al\TimingWheel\Contract\TimedInvocationInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;
use Swoole\Server;

abstract class AbstractTimerRegister implements ListenerInterface, TimedInvocationInterface
{
    /**
     * @var string
     */
    protected $annotation;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Server
     */
    protected $server;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [];
    }

    public function process(object $event)
    {
        $this->server = $event->server;

        [$classes, $methods] = $this->getAnnotationDelayInvocations($this->annotation);

        $this->registerClassesDelayInvocations($classes);
        $this->registerMethodsDelayInvocations($methods);
    }

    protected function getAnnotationDelayInvocations(string $annotation): array
    {
        return [
            AnnotationCollector::getClassesByAnnotation($annotation),
            AnnotationCollector::getMethodsByAnnotation($annotation),
        ];
    }

    protected function registerClassesDelayInvocations(array $classes)
    {
        $invokeMethodName = $this->getInvokeMethodName();
        foreach ($classes as $className => $annotation) {
            $classRef = ReflectionManager::reflectClass($className);
            if ($classRef->hasMethod($invokeMethodName) && value(
                    fn($methodRef) => $methodRef->isPublic(),
                    ReflectionManager::reflectMethod($className, $invokeMethodName))
            ) {
                $this->registerInvocation($annotation->getScheduleDueTime(), $className, $invokeMethodName);
            }
        }
    }

    protected function registerMethodsDelayInvocations(array $methods)
    {
        foreach ($methods as $method) {
            [$className, $methodName, $annotation] = array_values($method);
            $this->registerInvocation($annotation->getScheduleDueTime(), $className, $methodName);
        }
    }

    protected function getServer(): Server
    {
        return $this->server;
    }
}