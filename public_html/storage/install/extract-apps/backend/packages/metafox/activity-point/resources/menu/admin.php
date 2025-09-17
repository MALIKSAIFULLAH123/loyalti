<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'activitypoint.admin',
        'name'     => 'settings',
        'label'    => 'activitypoint::phrase.settings',
        'ordering' => 1,
        'to'       => '/activitypoint/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'activitypoint.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/activitypoint/permission',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'activitypoint.admin',
        'name'     => 'point_settings',
        'label'    => 'activitypoint::phrase.point_settings',
        'ordering' => 3,
        'to'       => '/activitypoint/setting/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'activitypoint.admin',
        'name'     => 'manage_packages',
        'label'    => 'activitypoint::phrase.manage_packages',
        'ordering' => 4,
        'to'       => '/activitypoint/package/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'activitypoint.admin',
        'name'     => 'point_transactions',
        'label'    => 'activitypoint::phrase.transaction_history',
        'ordering' => 6,
        'to'       => '/activitypoint/transaction/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'activitypoint.admin',
        'name'     => 'package_transactions',
        'label'    => 'activitypoint::phrase.package_transactions',
        'ordering' => 7,
        'to'       => '/activitypoint/package-transaction/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'activitypoint.admin',
        'name'     => 'point_members',
        'label'    => 'activitypoint::phrase.point_members',
        'ordering' => 8,
        'to'       => '/activitypoint/statistic/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'activitypoint.admin',
        'name'     => 'conversion_requests',
        'label'    => 'activitypoint::phrase.conversion_requests',
        'to'       => '/activitypoint/conversion-request/browse',
        'ordering' => 9,
    ],
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'activitypoint',
        'label'       => 'activitypoint::phrase.activity_point',
        'ordering'    => 1,
        'to'          => '/activitypoint/package/browse',
    ],
];
