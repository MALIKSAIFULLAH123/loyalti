<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'ewallet',
        'parent_name' => 'app-settings',
        'label'       => 'ewallet::phrase.app_name',
        'to'          => '/ewallet/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'ewallet.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'to'       => '/ewallet/setting',
        'ordering' => 1,
    ],
    [
        'menu'     => 'ewallet.admin',
        'name'     => 'withdraw_request',
        'label'    => 'ewallet::admin.withdraw_requests',
        'to'       => '/ewallet/request/browse',
        'ordering' => 2,
    ],
    [
        'menu'     => 'ewallet.admin',
        'name'     => 'transaction',
        'label'    => 'ewallet::web.ewallet_transactions',
        'to'       => '/ewallet/transaction/browse',
        'ordering' => 2,
    ],
    [
        'menu'     => 'ewallet.admin',
        'name'     => 'exchange_rate',
        'label'    => 'ewallet::admin.exchange_rates',
        'to'       => '/ewallet/exchange-rate/browse',
        'ordering' => 3,
    ],
    [
        'menu'     => 'ewallet.admin',
        'name'     => 'conversion_provider',
        'label'    => 'ewallet::admin.conversion_providers',
        'to'       => '/ewallet/conversion-provider/browse',
        'ordering' => 4,
    ],
    [
        'menu'     => 'ewallet.admin',
        'name'     => 'withdraw_provider',
        'label'    => 'ewallet::admin.withdrawal_providers',
        'to'       => '/ewallet/withdraw-provider/browse',
        'ordering' => 5,
    ],
    [
        'menu'     => 'ewallet.admin',
        'name'     => 'user_balance',
        'label'    => 'ewallet::admin.user_balances',
        'to'       => '/ewallet/user-balance/browse',
        'ordering' => 6,
    ],
];
