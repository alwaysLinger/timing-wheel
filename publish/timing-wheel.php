<?php

use Al\TimingWheel\TimingWheelManager;

return [
    'delay_method_name' => TimingWheelManager::Invoke_Method,
    'loop_method_name' => TimingWheelManager::Invoke_Method,

    /*
     * Use hyperf nacos component try to communicate with user processes to update config.
     * So this timing-wheel process will get a config message,
     * and this will block the coroutine because of the custom protocols
     *
     * So this component can update config from nacos itself if you want
    */
    'auto_update_config' => false,

    'protocols' => [
        'manager' => [
            'package_operate_type' => 'C',
            'package_header_offset' => 1,
            'package_length_type' => 'N',
            'package_body_offset' => 5,
        ],
    ],
];
