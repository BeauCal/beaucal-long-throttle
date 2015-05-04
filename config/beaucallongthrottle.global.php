<?php

/**
 * If overriding anything, drop into ./config/autoload/
 */
$dbAdapter = [
//    'use_transactions' => true,
//    'db_adapter_class' => 'Zend\Db\Adapter\Adapter',
//    'db_table' => 'beaucal_throttle',
//    'db_date_time_format' => 'Y-m-d H:i:s'
];

return [
    'beaucallongthrottle' => [
        'throttle' => [
//            'adapter' => 'BeaucalLongThrottle\Adapter\Db',
//
//            After setting a lock, verify its existence.
//            'verify_lock' => true,
        ],
        'BeaucalLongThrottle\Adapter\Db' => $dbAdapter,
    ],
];
