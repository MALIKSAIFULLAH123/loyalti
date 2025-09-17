<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'setting.payment.has_buyer_configurable_payment_gateways'],
        ],
        'menu'     => 'user.settingMenu',
        'name'     => 'payment_settings',
        'label'    => 'payment::web.payment_settings',
        'ordering' => 4,
        'as'       => 'sidebarHeading',
    ],
];
