<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'payment',
        'label'       => 'payment::phrase.payment',
        'ordering'    => 19,
        'to'          => '/payment/order/browse',
    ],
    [
        'menu'       => 'payment.admin',
        'name'       => 'payment_settings',
        'label'      => 'payment::phrase.settings',
        'ordering'   => 0,
        'to'         => '/payment/setting',
        'is_active'  => 0,
        'is_deleted' => 1,
    ],
    [
        'menu'       => 'payment.admin',
        'name'       => 'payment_manage_gateways',
        'label'      => 'payment::phrase.manage_gateways',
        'ordering'   => 0,
        'to'         => '/payment/gateway/browse',
        'is_deleted' => true,
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'payment.admin',
        'name'     => 'gateways',
        'label'    => 'payment::admin.gateways',
        'ordering' => 1,
        'to'       => '/payment/gateway/browse',
    ],
    [
        'menu'     => 'payment.admin',
        'name'     => 'orders',
        'label'    => 'payment::admin.orders',
        'ordering' => 2,
        'to'       => '/payment/order/browse',
    ],
    [
        'menu'     => 'payment.admin',
        'name'     => 'transactions',
        'label'    => 'payment::admin.transactions',
        'ordering' => 3,
        'to'       => '/payment/transaction/browse',
    ],
];
