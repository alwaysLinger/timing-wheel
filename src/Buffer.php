<?php

namespace Al\TimingWheel;

use Al\TimingWheel\Contract\PackerInterface;

class Buffer
{
    /**
     * @var int
     */
    private $headLen = 0;

    /**
     * @var PackerInterface
     */
    private $packer;

    /**
     * @var string
     */
    private $buffer = '';

    public function __construct(PackerInterface $packer)
    {
        $this->packer = $packer;
        $this->headLen = $packer->headLen();
    }

    public function append(string $messages)
    {
        $this->buffer .= $messages;
    }

    public function getOne()
    {
        if (!$this->atLeastOne()) {
            return false;
        }

        $msgLen = $this->packer->payloadLen($this->buffer);

        if (!$this->hasOne($msgLen)) {
            return false;
        }

        $buffer = substr($this->buffer, $this->headLen, $msgLen);
        $this->setBuffer(substr($this->buffer, $this->headLen + $msgLen));

        return $this->packer->unpack($buffer);
    }

    public function setBuffer(string $buf)
    {
        $this->buffer = $buf;
    }

    private function atLeastOne(): bool
    {
        return strlen($this->buffer) > $this->headLen;
    }

    private function hasOne(int $msgLen): bool
    {
        return strlen($this->buffer) >= $this->headLen + $msgLen;
    }
}