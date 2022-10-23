<?php

namespace Al\TimingWheel;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class PackerConfig
{
    private $protocols = [];

    public function __construct(ContainerInterface $container)
    {
        $this->protocols = $container->get(ConfigInterface::class)->get(
            'timing-wheel.protocols.manager',
            [
                'package_operate_type' => 'C',
                'package_header_offset' => 1,
                'package_length_type' => 'N',
                'package_body_offset' => 5,
            ]
        );
    }

    public function getConfig(): array
    {
        return $this->protocols;
    }
}