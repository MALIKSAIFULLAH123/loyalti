<?php

/**
 * stub: packages/config/config.stub.
 */

use MetaFox\InAppPurchase\Support\InAppPurchaseGateway;

return [
    'name'     => 'In-App Purchase',
    'gateways' => [
        [
            'service'     => 'in-app-purchase',
            'module_id'   => 'in-app-purchase',
            'is_active'   => 0,
            'is_test'     => 0,
            'title'       => 'In-App Purchase',
            'description' => '',
            'config'      => [
                'icon' => 'ico-creditcard',
            ],
            'service_class' => InAppPurchaseGateway::class,
        ],
    ],
];
