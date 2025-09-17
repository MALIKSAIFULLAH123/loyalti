<?php

/* this is auto generated file */
return [
    [
        'menu'     => 'core.accountMenu',
        'name'     => 'ewallet',
        'label'    => 'ewallet::phrase.app_name',
        'ordering' => 2,
        'icon'     => 'ico-money-bag',
        'to'       => '/ewallet',
    ],
    [
        'tab'      => 'landing',
        'menu'     => 'ewallet.sidebarMenu',
        'name'     => 'landing',
        'label'    => 'ewallet::web.insights',
        'ordering' => 1,
        'icon'     => 'ico-money-bag-o',
        'to'       => '/ewallet',
    ],
    [
        'tab'      => 'transaction',
        'menu'     => 'ewallet.sidebarMenu',
        'name'     => 'transaction',
        'label'    => 'ewallet::web.ewallet_transactions',
        'ordering' => 2,
        'icon'     => 'ico-list-o',
        'to'       => '/ewallet/transaction',
    ],
    [
        'tab'      => 'request',
        'menu'     => 'ewallet.sidebarMenu',
        'name'     => 'request',
        'label'    => 'ewallet::phrase.withdrawal_requests',
        'ordering' => 3,
        'icon'     => 'ico-list-bullet-o',
        'to'       => '/ewallet/request',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn'],
        ],
        'buttonProps' => [
            'fullWidth' => true,
            'color'     => 'primary',
            'variant'   => 'contained',
        ],
        'menu'     => 'ewallet.sidebarMenu',
        'name'     => 'add',
        'label'    => 'ewallet::phrase.withdrawal_request',
        'ordering' => 4,
        'as'       => 'sidebarButton',
        'icon'     => 'ico-plus',
        'value'    => 'ewallet/newRequest',
        'to'       => '',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_cancel'],
        ],
        'menu'     => 'ewallet.ewallet_withdraw_request.itemActionMenu',
        'name'     => 'cancel',
        'label'    => 'ewallet::phrase.cancel_request',
        'ordering' => 1,
        'value'    => 'ewallet/cancelItem',
        'icon'     => 'ico-close-circle-o',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_view_reason'],
        ],
        'menu'     => 'ewallet.ewallet_withdraw_request.itemActionMenu',
        'name'     => 'view_reason',
        'label'    => 'ewallet::admin.view_reason',
        'ordering' => 2,
        'value'    => 'ewallet/viewReason',
        'icon'     => 'ico-info-circle-alt-o',
    ],
];
