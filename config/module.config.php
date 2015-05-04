<?php

return [
    'service_manager' => [
        'factories' => [
            'BeaucalLongThrottle\Service\Throttle' => 'BeaucalLongThrottle\Factory\ThrottleFactory',
            'BeaucalLongThrottle\Options\Throttle' => 'BeaucalLongThrottle\Factory\ThrottleOptionsFactory',
            'BeaucalLongThrottle\Adapter\Db' => 'BeaucalLongThrottle\Factory\DbAdapterFactory',
            'BeaucalLongThrottle\Options\DbAdapter' => 'BeaucalLongThrottle\Factory\DbAdapterOptionsFactory',
        ],
    ],
];
