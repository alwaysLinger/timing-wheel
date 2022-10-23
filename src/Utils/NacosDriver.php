<?php

namespace Al\TimingWheel\Utils;

use Hyperf\ConfigNacos\NacosDriver as HyperfDriver;

class NacosDriver extends HyperfDriver
{
    public function updateConfig(array $config)
    {
        foreach ($config as $key => $value) {
            if (is_string($key)) {
                $this->config->set($key, $value);
            }
        }
    }
}