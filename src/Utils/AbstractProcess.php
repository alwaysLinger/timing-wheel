<?php

namespace Al\TimingWheel\Utils;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\AbstractProcess as HyperfAbstractProcess;
use Hyperf\Process\Event\AfterProcessHandle;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Process\Exception\SocketAcceptException;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Swoole\Process as SwooleProcess;
use Swoole\Server;
use Swoole\Timer;

abstract class AbstractProcess extends HyperfAbstractProcess
{
    public const Process_Name = 'timing-wheel-manager';
    public const Invoke_Method = 'dueProcess';

    protected $recvTimeout = 5.0;

    protected function closeSocket()
    {
        $this->process->exportSocket()->close();
    }

    protected function setProcessName(string $name)
    {
        $this->process->name($name);
    }

    // override this method to avoid hyperf IPC messages that we do not want
    // such as update config message
    // because we can do that in our own process
    protected function bindServer(Server $server): void
    {
        $process = new SwooleProcess(function (SwooleProcess $process) {
            try {
                $this->event && $this->event->dispatch(new BeforeProcessHandle($this, 0));
                $this->process = $process;

                $this->setProcessName($this->name);
                $this->handle();
            } catch (\Throwable $throwable) {
                $this->logThrowable($throwable);
            } finally {
                $this->closeSocket();
                $this->event && $this->event->dispatch(new AfterProcessHandle($this, 0));

                ProcessManager::clear();
                Timer::clearAll();
                CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
                sleep($this->restartInterval);
            }
        }, false, SOCK_STREAM, true);

        $server->addProcess($process);

        ProcessManager::setTimingManager($process);
        // ProcessCollector::add(static::Process_Name, $process);
    }

    public function handle(): void
    {
        $this->autoSyncConfig();

        $this->dispatch();
    }

    protected function autoSyncConfig()
    {
        if ($this->shouldUpdateConfigFromNacos()) {
            Timer::tick(5 * 1000, fn() => $this->container->get(NacosDriver::class)->fetchConfig());
        }
    }

    protected function shouldUpdateConfigFromNacos(): bool
    {
        return $this->container->get(ConfigInterface::class)->get('timing-wheel.auto_update_config', false);
    }

    protected function logThrowable(\Throwable $throwable): void
    {
        $logger = $this->container->get(StdoutLoggerInterface::class);
        $logger->error((new TimingWheelExceptionFormatter())->format($throwable));

        if ($throwable instanceof SocketAcceptException) {
            $logger->critical('Socket of process is unavailable, please restart the server');
        }
    }
}