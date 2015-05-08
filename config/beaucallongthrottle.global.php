<?php

/**
 * Drop into ./config/autoload/
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

$throttle = [
//    'separator' => '::',
//
//    To allow for multiple locks instead, use Adapter\DbMultiple
//    'adapter_class' => 'BeaucalLongThrottle\Adapter\Db',
//
//    After setting a lock, verify its existence.  Leave true if at all possible.
//    'verify_lock' => true,
];

/**
 * Regex used by Adapter\DbMultiple to match key => maximum count
 * Try not to have maximum count > hundreds, if needed reduce lock term.
 * e.g. think 50/hour, not 1000/day.
 *
 * Any locks that don't match here are treated as maximum count => 1
 */
$regexCounts = [
// '/^test(-[0-9]+)?$/' => 10
];

/**
 * Edit above only.
 */
$dbMultipleAdapter = $dbAdapter;
$dbAdapter['options_class'] = 'BeaucalLongThrottle\Options\DbAdapter';
$dbMultipleAdapter['options_class'] = 'BeaucalLongThrottle\Options\DbMultipleAdapter';
$dbMultipleAdapter['regex_counts'] = $regexCounts;
return [
    'beaucallongthrottle' => [
        'throttle' => $throttle,
        'BeaucalLongThrottle\Adapter\Db' => $dbAdapter,
        'BeaucalLongThrottle\Adapter\DbMultiple' => $dbMultipleAdapter,
    ],
];
