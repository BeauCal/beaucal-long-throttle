<?php

$dbAdapter = [
    'use_transactions' => 'use_transactions_another',
    'db_adapter_class' => 'db_adapter_class_another',
    'db_table' => 'db_table_another',
    'db_date_time_format' => 'db_date_time_format_another',
    'clear_all_is_cheap' => 'clear_all_is_cheap_another'
];

$apcAdapter = [
    'apc_namespace' => 'apc_namespace_another',
];

$throttle = [
    'separator' => 'separator_another',
    'adapter_class' => 'adapter_class_another',
    'verify_lock' => 'verify_lock_another',
];

$regexCounts = [
    'regex_counts_another' => 99
];

$dbMultipleAdapter = $dbAdapter;
$dbAdapter['options_class'] = 'BeaucalLongThrottle\Options\DbAdapter';
$dbMultipleAdapter['options_class'] = 'BeaucalLongThrottle\Options\DbMultipleAdapter';
$dbMultipleAdapter['regex_counts'] = $regexCounts;
return [
    'beaucallongthrottle' => [
        'throttle' => $throttle,
        'BeaucalLongThrottle\Adapter\Db' => $dbAdapter,
        'BeaucalLongThrottle\Adapter\DbMultiple' => $dbMultipleAdapter,
        'BeaucalLongThrottle\Adapter\Apc' => $apcAdapter,
    ],
];
