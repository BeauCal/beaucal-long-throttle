<?php

return [
    'service_manager' => [
        'aliases' => [
            'BeaucalLongThrottle' => 'BeaucalLongThrottle\Service\Throttle',
        ],
        'factories' => [
            'beaucallongthrottle_config' => 'BeaucalLongThrottle\Factory\ConfigFactory',
            'BeaucalLongThrottle\Service\Throttle' => 'BeaucalLongThrottle\Factory\ThrottleFactory',
            'BeaucalLongThrottle\Options\Throttle' => 'BeaucalLongThrottle\Factory\ThrottleOptionsFactory',
            'BeaucalLongThrottle\Adapter\Db' => 'BeaucalLongThrottle\Factory\DbAdapterFactory',
            'BeaucalLongThrottle\Options\DbAdapter' => 'BeaucalLongThrottle\Factory\DbAdapterOptionsFactory',
        ],
    ],
];
