<?php

return [
    'default'  => env('MFOX_SMS_PROVIDER', 'log'),
    'services' => [
        'log' => [
            'service' => 'log',
            'channel' => env('SMS_LOG_CHANNEL'),
        ],
    ],
    'test_number' => env('MFOX_SMS_TEST_NUMBER', null),
];
