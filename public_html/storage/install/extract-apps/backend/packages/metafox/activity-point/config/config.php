<?php

use MetaFox\ActivityPoint\Models\PointPackage;
use MetaFox\ActivityPoint\Support\Gateways\ActivityPoint;

return [
    'shareAssets' => [
        'images/package_no_image.png' => 'package_no_image',
        'images/get_started.png'      => 'get_started',
        'images/get_started_dark.png' => 'get_started_dark',
    ],
    'name'     => 'ActivityPoint',
    'gateways' => [
        [
            'service'     => ActivityPoint::GATEWAY_SERVICE_NAME,
            'module_id'   => 'activitypoint',
            'is_active'   => 0,
            'is_test'     => 0,
            'title'       => 'Activity Point',
            'description' => 'Activity Point Payment Gateway',
            'config'      => [
                'icon'        => 'ico-star-circle-o',
                'filter_mode' => 'blacklist',
            ],
            'service_class' => ActivityPoint::class,
            'filters'       => [
                PointPackage::ENTITY_TYPE,
                'ewallet_withdraw_request',
            ],
        ],
    ],
];
