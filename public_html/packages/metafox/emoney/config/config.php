<?php

/**
 * stub: packages/config/config.stub.
 */

return [
    'gateways' => [
        [
            'service'     => \MetaFox\EMoney\Support\Gateway\EwalletPaymentGateway::GATEWAY_SERVICE_NAME,
            'module_id'   => 'ewallet',
            'is_active'   => 0,
            'is_test'     => 0,
            'title'       => 'E-Wallet',
            'description' => 'E-Wallet Payment Gateway',
            'config'      => [
                'icon'        => 'ico-money-bag-o',
                'filter_mode' => 'blacklist',
            ],
            'service_class' => \MetaFox\EMoney\Support\Gateway\EwalletPaymentGateway::class,
            'filters'       => [],
        ],
    ],
];
