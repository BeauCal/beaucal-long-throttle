<?php

return [
    'service_manager' => [
        'aliases' => [
            'BeaucalLongThrottle' => 'BeaucalLongThrottle\Service\Throttle',
        ],
        'abstract_factories' => [
            'BeaucalLongThrottle\Factory\DbAdapterAbstractFactory',
        ],
        'factories' => [
            'beaucallongthrottle_config' => 'BeaucalLongThrottle\Factory\ConfigFactory',
            'beaucallongthrottle_apc' => 'BeaucalLongThrottle\Factory\ApcThrottleFactory',
            'BeaucalLongThrottle\Service\Throttle' => 'BeaucalLongThrottle\Factory\ThrottleFactory',
            'BeaucalLongThrottle\Options\Throttle' => 'BeaucalLongThrottle\Factory\ThrottleOptionsFactory',
            'BeaucalLongThrottle\Adapter\Apc' => 'BeaucalLongThrottle\Factory\ApcAdapterFactory'
        ],
    ],
];
