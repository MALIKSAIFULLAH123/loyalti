<?php

return [
    'gateways' => [
        'paypal' => [
            'service'     => 'paypal',
            'module_id'   => 'paypal',
            'is_active'   => env('PAYMENT_PAYPAL_ACTIVE', 0),
            'is_test'     => env('PAYMENT_PAYPAL_SANDBOX', 1),
            'title'       => 'PayPal',
            'description' => 'PayPal Payment Gateway',
            'config'      => [
                'client_id'     => env('PAYMENT_PAYPAL_CLIENT_ID', ''),
                'client_secret' => env('PAYMENT_PAYPAL_CLIENT_SECRET', ''),
                'webhook_id'    => env('PAYMENT_PAYPAL_WEBHOOK_ID', ''),
                'icon'          => 'ico-paypal',
            ],
            'service_class' => \MetaFox\Paypal\Support\Paypal::class,
            'enable_seller_config' => true,
        ],
    ],
    'withdraw_methods' => [
        'paypal' => [
            'title'         => 'paypal::phrase.paypal',
            'description'   => null,
            'service'       => 'paypal',
            'service_class' => \MetaFox\Paypal\Support\Withdraws\Paypal::class,
            'module_id'     => 'paypal',
            'is_active'     => true,
        ],
    ],
];
