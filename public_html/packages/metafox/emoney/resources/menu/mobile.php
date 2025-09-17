    <?php

return [
    //Need to mobile old version < 5.1.6
    [
        'params' => [
            'module_name'   => 'emoney',
            'resource_name' => 'emoney_statistic',
        ],
        'tab'      => 'landing',
        'menu'     => 'emoney.sidebarMenu',
        'name'     => 'landing',
        'label'    => 'ewallet::web.insights',
        'ordering' => 1,
        'to'       => '/emoney',
        'value'    => 'viewItem',
    ],
    [
        'params' => [
            'module_name'   => 'emoney',
            'resource_name' => 'emoney_transaction',
        ],
        'tab'      => 'transaction',
        'menu'     => 'emoney.sidebarMenu',
        'name'     => 'transaction',
        'label'    => 'ewallet::web.incoming_transactions',
        'ordering' => 2,
        'to'       => '/emoney/transaction',
        'value'    => 'viewAll',
    ],
    [
        'params' => [
            'module_name'   => 'emoney',
            'resource_name' => 'emoney_withdraw_request',
        ],
        'tab'      => 'request',
        'menu'     => 'emoney.sidebarMenu',
        'name'     => 'request',
        'label'    => 'ewallet::phrase.withdrawal_requests',
        'ordering' => 3,
        'to'       => '/emoney/request',
        'value'    => 'viewAll',
    ],
    [
        'showWhen'  => [
            'and',
            ['falsy', 'apiVersion']
        ],
        'menu'      => 'core.bodyMenu',
        'name'      => 'ewallet',
        'label'     => 'ewallet::phrase.app_name',
        'ordering'  => 3,
        'value'     => '',
        'to'        => '/emoney',
        'as'        => 'item',
        'icon'      => 'money-bag',
        'iconColor' => '#0C8001',
    ],
    [
        'params' => [
            'module_name'   => 'emoney',
            'resource_name' => 'emoney_withdraw_request',
        ],
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_cancel'],
        ],
        'menu'     => 'emoney.emoney_withdraw_request.itemActionMenu',
        'name'     => 'cancel',
        'label'    => 'ewallet::phrase.cancel_request',
        'ordering' => 1,
        'value'    => 'emoney/cancelItem',
    ],
    [
        'params' => [
            'module_name'   => 'emoney',
            'resource_name' => 'emoney_withdraw_request',
        ],
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_view_reason'],
        ],
        'menu'     => 'emoney.emoney_withdraw_request.itemActionMenu',
        'name'     => 'view_reason',
        'label'    => 'ewallet::admin.view_reason',
        'ordering' => 2,
        'value'    => 'viewReason',
    ],
    [
        'params' => [
            'module_name'   => 'emoney',
            'resource_name' => 'emoney_withdraw_request',
        ],
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_cancel'],
        ],
        'menu'     => 'emoney.emoney_withdraw_request.detailActionMenu',
        'name'     => 'cancel',
        'label'    => 'ewallet::phrase.cancel_request',
        'ordering' => 1,
        'value'    => 'emoney/cancelItem',
    ],
    [
        'params' => [
            'module_name'   => 'emoney',
            'resource_name' => 'emoney_withdraw_request',
        ],
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_view_reason'],
        ],
        'menu'     => 'emoney.emoney_withdraw_request.detailActionMenu',
        'name'     => 'view_reason',
        'label'    => 'ewallet::admin.view_reason',
        'ordering' => 2,
        'value'    => 'viewReason',
    ],


    //Need for mobile version >= 5.1.6
    [
        'params' => [
            'module_name'   => 'ewallet',
            'resource_name' => 'ewallet_statistic',
        ],
        'tab'      => 'landing',
        'menu'     => 'ewallet.sidebarMenu',
        'name'     => 'landing',
        'label'    => 'ewallet::web.insights',
        'ordering' => 1,
        'to'       => '/ewallet',
        'value'    => 'viewItem',
    ],
    [
        'params' => [
            'module_name'   => 'ewallet',
            'resource_name' => 'ewallet_transaction',
        ],
        'tab'      => 'transaction',
        'menu'     => 'ewallet.sidebarMenu',
        'name'     => 'transaction',
        'label'    => 'ewallet::web.ewallet_transactions',
        'ordering' => 2,
        'to'       => '/ewallet/transaction',
        'value'    => 'viewAll',
    ],
    [
        'params' => [
            'module_name'   => 'ewallet',
            'resource_name' => 'ewallet_withdraw_request',
        ],
        'tab'      => 'request',
        'menu'     => 'ewallet.sidebarMenu',
        'name'     => 'request',
        'label'    => 'ewallet::phrase.withdrawal_requests',
        'ordering' => 3,
        'to'       => '/ewallet/request',
        'value'    => 'viewAll',
    ],
    [
        'showWhen'  => [
            'and',
            ['gte', 'apiVersion', 'v1.6']
        ],
        'menu'      => 'core.bodyMenu',
        'name'      => 'ewallet_new',
        'label'     => 'ewallet::phrase.app_name',
        'ordering'  => 3,
        'value'     => '',
        'to'        => '/ewallet',
        'as'        => 'item',
        'icon'      => 'money-bag',
        'iconColor' => '#0C8001',
    ],
    [
        'params' => [
            'module_name'   => 'ewallet',
            'resource_name' => 'ewallet_withdraw_request',
        ],
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_cancel'],
        ],
        'menu'     => 'ewallet.ewallet_withdraw_request.itemActionMenu',
        'name'     => 'cancel',
        'label'    => 'ewallet::phrase.cancel_request',
        'ordering' => 1,
        'value'    => 'ewallet/cancelItem',
    ],
    [
        'params' => [
            'module_name'   => 'ewallet',
            'resource_name' => 'ewallet_withdraw_request',
        ],
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_view_reason'],
        ],
        'menu'     => 'ewallet.ewallet_withdraw_request.itemActionMenu',
        'name'     => 'view_reason',
        'label'    => 'ewallet::admin.view_reason',
        'ordering' => 2,
        'value'    => 'viewReason',
    ],
    [
        'params' => [
            'module_name'   => 'ewallet',
            'resource_name' => 'ewallet_withdraw_request',
        ],
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_cancel'],
        ],
        'menu'     => 'ewallet.ewallet_withdraw_request.detailActionMenu',
        'name'     => 'cancel',
        'label'    => 'ewallet::phrase.cancel_request',
        'ordering' => 1,
        'value'    => 'ewallet/cancelItem',
    ],
    [
        'params' => [
            'module_name'   => 'ewallet',
            'resource_name' => 'ewallet_withdraw_request',
        ],
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_view_reason'],
        ],
        'menu'     => 'ewallet.ewallet_withdraw_request.detailActionMenu',
        'name'     => 'view_reason',
        'label'    => 'ewallet::admin.view_reason',
        'ordering' => 2,
        'value'    => 'viewReason',
    ],
];
