<?php

/**
 * If overriding anything, drop into ./config/autoload/
 */
$dbAdapter = [
//    'use_transactions' => true,
//    'db_adapter_class' => 'Zend\Db\Adapter\Adapter',
//    'db_table' => 'beaucal_throttle',
//    'db_date_time_format' => 'Y-m-d H:i:s'
//
//    When clearing all expired locks is cheap, do it with every lock-set
//    'clear_all_is_cheap' => true
];

return [
    'beaucallongthrottle' => [
        'throttle' => [
//            'adapter_class' => 'BeaucalLongThrottle\Adapter\Db',
//
//            After setting a lock, verify its existence.
//            'verify_lock' => true,
        ],
        'BeaucalLongThrottle\Adapter\Db' => $dbAdapter,
    ],
];
