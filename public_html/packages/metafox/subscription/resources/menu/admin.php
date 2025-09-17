<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'subscription',
        'label'       => 'subscription::phrase.subscription',
        'ordering'    => 24,
        'to'          => '/subscription/package/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'subscription.admin',
        'name'     => 'site_setting',
        'label'    => 'core::phrase.settings',
        'ordering' => 1,
        'to'       => '/subscription/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'subscription.admin',
        'name'     => 'manage_packages',
        'label'    => 'subscription::phrase.subscription_admin_menu_manage_packages',
        'ordering' => 3,
        'to'       => '/subscription/package/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'subscription.admin',
        'name'     => 'manage_subscriptions',
        'label'    => 'subscription::phrase.subscription_admin_menu_manage_subscriptions',
        'ordering' => 5,
        'to'       => '/subscription/invoice/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'subscription.admin',
        'name'     => 'comparison',
        'label'    => 'subscription::phrase.subscription_admin_menu_manage_comparisons',
        'ordering' => 6,
        'to'       => '/subscription/comparison/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'subscription.admin',
        'name'     => 'cancel_reasons',
        'label'    => 'subscription::phrase.subscription_admin_menu_manage_reasons',
        'ordering' => 8,
        'to'       => '/subscription/cancel-reason/browse',
    ],
];
