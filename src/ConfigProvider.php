<?php

namespace Al\TimingWheel;

use Al\TimingWheel\Contract\PackerInterface;
use Al\TimingWheel\Packer\StreamPacker;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'dependencies' => [
                PackerInterface::class => StreamPacker::class,
            ],
            'publish' => [
                [
                    'id' => 'timing-wheel',
                    'description' => 'The config for single-flight.',
                    'source' => __DIR__ . '/../publish/timing-wheel.php',
                    'destination' => BASE_PATH . '/config/autoload/timing-wheel.php',
                ],
            ],
        ];
    }
}