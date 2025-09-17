<?php

/**
 * stub: packages/config/config.stub.
 */

use MetaFox\Mfa\Support\Services\Authenticator;
use MetaFox\Mfa\Support\Services\Email;
use MetaFox\Mfa\Support\Services\Sms;

return [
    'mfa_services' => [
        'authenticator' => [
            'name'          => 'authenticator',
            'label'         => 'Authenticator',
            'service_class' => Authenticator::class,
            'is_active'     => 1,
            'config'        => [
                'icon' => [
                    'web'    => 'ico-key',
                    'mobile' => 'key',
                ],
            ],
        ],
        'email' => [
            'name'          => 'email',
            'label'         => 'Email Authentication',
            'service_class' => Email::class,
            'is_active'     => 1,
            'config'        => [
                'icon' => [
                    'web'    => 'ico-envelope-o',
                    'mobile' => 'envelope-o',
                ],
            ],
        ],
        'sms' => [
            'name'          => 'sms',
            'label'         => 'SMS Authentication',
            'service_class' => Sms::class,
            'is_active'     => 1,
            'config'        => [
                'icon' => [
                    'web'    => 'ico-comment-square-o',
                    'mobile' => 'comment-square-o',
                ],
            ],
        ],
    ],
];
