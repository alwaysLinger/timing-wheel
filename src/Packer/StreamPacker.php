<?php

namespace Al\TimingWheel\Packer;

use Al\TimingWheel\Contract\PackerInterface;
use Al\TimingWheel\Exception\UnAcceptedMessagesException;
use Al\TimingWheel\PackerConfig;

class StreamPacker implements PackerInterface
{
    private array $protocols;

    public function __construct(PackerConfig $config)
    {
        $this->protocols = $config->getConfig();
    }

    public function headLen(): int
    {
        return (int)$this->protocols['package_body_offset'];
    }


    public function payloadLen(string $buffer): int
    {
        return unpack(
            $this->protocols['package_length_type'],
            substr(
                $buffer,
                $this->protocols['package_header_offset'],
                $this->protocols['package_body_offset'] - $this->protocols['package_header_offset']
            )
        )[1];
    }

    public function pack(array $messages): string
    {
        return $this->serialize($messages);
    }

    /**
     * @param string $buffer
     * @return array [operate, payload]
     */
    public function unpack(string $buffer): array
    {
        return $this->unserialize($buffer);
    }

    protected function serialize(array $messages): string
    {
        $messages = $this->formatMessages($messages);

        $serialized = '';
        foreach ($messages as $message) {
            $serialized .= value(
                fn($serialized) => pack(
                        $this->protocols['package_operate_type'] . $this->protocols['package_length_type'],
                        $message['operate'],
                        strlen($serialized)
                    ) . $serialized,
                json_encode($message, JSON_UNESCAPED_UNICODE)
            );
        }

        return $serialized;
    }

    protected function unserialize(string $message): array
    {
        return json_decode($message, true);
    }

    private function formatMessages(array $messages): array
    {
        return array_map(fn($message) => tap(
            $message,
            fn($message) => throw_unless(
                array_key_exists('operate', $message) && array_key_exists('payload', $message),
                UnAcceptedMessagesException::class,
            )
        ), $messages);
    }
}