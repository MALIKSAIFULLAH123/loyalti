<?php

return [
    'test_email' => [
        'is_public' => 0,
        'env_var'   => 'MFOX_MAIL_TEST_EMAIL',
        'value'     => '',
    ],
    'from.address' => [
        'is_public' => 0,
        'env_var'   => 'MFOX_MAIL_FROM_ADDRESS',
        'value'     => '',
    ],
    'from.name' => [
        'is_public' => 0,
        'env_var'   => 'MFOX_MAIL_FROM_NAME',
        'value'     => '',
    ],
    'queue' => [
        'is_public' => 0,
        'env_var'   => 'MFOX_MAIL_QUEUE',
        'value'     => true,
    ],
    'signature' => [
        'is_public' => 0,
        'env_var'   => 'MFOX_MAIL_SIGNATURE',
        'value'     => 'Kind Regards,',
    ],
    'mailers.smtp' => [
        'is_public'  => 0,
        'config_var' => 'mail',
        'value'      => config('mail.mailers.smtp'),
    ],
    'default' => [
        'is_public' => 0,
        'env_var'   => 'MFOX_MAIL_PROVIDER',
        'value'     => 'smtp',
    ],
    'dns_check' => [
        'is_public' => 0,
        'env_var'   => 'MFOX_MAIL_DNS_CHECK',
        'value'     => false,
    ],
];
