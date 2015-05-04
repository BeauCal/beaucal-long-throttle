<?php

$dbAdapter = [
    'use_transactions' => 'use_transactions_another',
    'db_adapter_class' => 'db_adapter_class_another',
    'db_table' => 'db_table_another',
    'db_date_time_format' => 'db_date_time_format_another',
    'clear_all_is_cheap' => 'clear_all_is_cheap_another'
];

return [
    'beaucallongthrottle' => [
        'throttle' => [
            'adapter_class' => 'adapter_class_another',
            'verify_lock' => 'verify_lock_another',
        ],
        'BeaucalLongThrottle\Adapter\Db' => $dbAdapter,
    ],
];
