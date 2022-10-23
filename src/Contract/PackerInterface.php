<?php

namespace Al\TimingWheel\Contract;

interface PackerInterface
{
    public function headLen(): int;

    public function payloadLen(string $buffer): int;

    public function pack(array $messages): string;

    public function unpack(string $buffer): array;
}