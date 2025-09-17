<?php

/* this is auto generated file */
return [
    [
        'showWhen'  => [],
        'menu'      => 'core.bodyMenu',
        'name'      => 'activity_point',
        'label'     => 'activitypoint::phrase.activity_points',
        'ordering'  => 2,
        'value'     => '',
        'to'        => '/activitypoint',
        'as'        => 'item',
        'icon'      => 'star-circle',
        'iconColor' => '#2681d5',
    ],
    [
        'params' => [
            'module_name'   => 'activitypoint',
            'resource_name' => 'activitypoint_conversion_request',
        ],
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_cancel'],
        ],
        'menu'     => 'activitypoint.activitypoint_conversion_request.itemActionMenu',
        'name'     => 'cancel',
        'label'    => 'activitypoint::phrase.cancel_request',
        'ordering' => 1,
        'value'    => 'activitypoint/cancelItem',
    ],
    [
        'params' => [
            'module_name'   => 'activitypoint',
            'resource_name' => 'activitypoint_conversion_request',
        ],
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_view_reason'],
        ],
        'menu'     => 'activitypoint.activitypoint_conversion_request.itemActionMenu',
        'name'     => 'view_reason',
        'label'    => 'activitypoint::admin.view_reason',
        'ordering' => 2,
        'value'    => 'activitypoint/viewReason',
    ],
    [
        'params' => [
            'module_name'   => 'activitypoint',
            'resource_name' => 'activitypoint_conversion_request',
        ],
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_cancel'],
        ],
        'menu'     => 'activitypoint.activitypoint_conversion_request.detailActionMenu',
        'name'     => 'cancel',
        'label'    => 'activitypoint::phrase.cancel_request',
        'ordering' => 1,
        'value'    => 'activitypoint/cancelItem',
    ],
    [
        'params' => [
            'module_name'   => 'activitypoint',
            'resource_name' => 'activitypoint_conversion_request',
        ],
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_view_reason'],
        ],
        'menu'     => 'activitypoint.activitypoint_conversion_request.detailActionMenu',
        'name'     => 'view_reason',
        'label'    => 'activitypoint::admin.view_reason',
        'ordering' => 2,
        'value'    => 'activitypoint/viewReason',
    ],

    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.activitypoint.activitypoint.can_purchase_points']
        ],
        'menu'     => 'activitypoint.sidebarMenu',
        'name'     => 'point_packages',
        'label'    => 'activitypoint::web.point_packages',
        'ordering' => 1,
        'to'       => 'viewPointPackages',
        'icon'     => 'folders-o'
    ],
    [
        'menu'     => 'activitypoint.sidebarMenu',
        'name'     => 'how_to_earns',
        'label'    => 'activitypoint::web.how_to_earns',
        'ordering' => 2,
        'to'       => 'viewEarnMorePoint',
        'icon'     => 'crown-o'
    ],
    [
        'menu'     => 'activitypoint.sidebarMenu',
        'name'     => 'transaction_history',
        'label'    => 'activitypoint::web.transaction_history',
        'ordering' => 3,
        'to'       => 'viewTransactionHistory',
        'icon'     => 'text-file-search'
    ],
    [
        'menu'     => 'activitypoint.sidebarMenu',
        'name'     => 'point_conversion_requests',
        'label'    => 'activitypoint::web.point_conversion_requests',
        'ordering' => 4,
        'to'       => 'activitypoint_conversion_request',
        'icon'     => 'list-bullet-o'
    ],
];
