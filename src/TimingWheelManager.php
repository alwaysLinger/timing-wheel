<?php

namespace Al\TimingWheel;

use Al\TimingWheel\Event\AddAnnotationTask;
use Al\TimingWheel\Exception\ManagerDispatchException;
use Al\TimingWheel\Utils\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Hyperf\Process\Exception\SocketAcceptException;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Socket;

/**
 * @Process(name=TimingWheelManager::Process_Name)
 */
class TimingWheelManager extends AbstractProcess
{
    /**
     * @var null|\Throwable
     */
    protected $throwable;

    /**
     * @var Channel
     */
    protected $quitChan;

    /**
     * @var Buffer
     */
    protected $buffer;

    /**
     * @var int
     */
    protected $timingWheelSlotsNum = 3600;

    /**
     * @var null|TaskTimingWheel
     */
    protected $timingWheel;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->buffer = $this->container->get(Buffer::class);
    }

    public function dispatch()
    {
        $this->quitChan = new Channel();

        go(fn() => $this->runTaskWheel());
        go(fn() => $this->dispatchMessage());

        $this->waitExit();
    }

    protected function dispatchMessage()
    {
        /** @var Socket $socket */
        $socket = $this->process->exportSocket();
        $socket->setOption(SOL_SOCKET, SO_RCVTIMEO, ['sec' => $this->recvTimeout, 'usec' => 0]);

        try {
            while (true) {
                $message = $socket->recv();

                if ($message === '') {
                    throw new SocketAcceptException('Socket is closed', $socket->errCode);
                }
                if ($message === false && $socket->errCode !== SOCKET_ETIMEDOUT) {
                    throw new SocketAcceptException('Socket is closed', $socket->errCode);
                }

                $this->handleMessages($message);
            }
        } catch (\Throwable $th) {
            $this->throwable = $th;
        } finally {
            $this->quitChan->push(1);
        }
    }

    protected function handleThrowable()
    {
        throw new ManagerDispatchException('something unexpected occurred during timing-wheel dispatching', 0, $this->throwable);
    }

    protected function waitExit()
    {
        $this->quitChan->pop();

        $this->handleThrowable();
    }

    protected function handleMessages(string $message)
    {
        if (!$message) {
            return;
        }

        $this->buffer->append($message);
        while ($task = $this->buffer->getOne()) {
            $this->schedule($task);
        }
    }

    private function schedule(array $task)
    {
        $this->getTimingWheel()->handleTask($task);
    }

    private function runTaskWheel()
    {
        $this->timingWheel = new TaskTimingWheel($this->timingWheelSlotsNum, $this->container);

        $this->event->dispatch(new AddAnnotationTask($this->process));
    }

    protected function getTimingWheel(): TaskTimingWheel
    {
        return $this->timingWheel;
    }
}