<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'marketplace',
        'label'       => 'marketplace::phrase.marketplace',
        'ordering'    => 16,
        'to'          => '/marketplace/marketplace/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'marketplace.admin',
        'name'     => 'settings',
        'label'    => 'marketplace::phrase.settings',
        'ordering' => 1,
        'to'       => '/marketplace/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'marketplace.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/marketplace/permission',
    ],
    [
        'menu'     => 'marketplace.admin',
        'name'     => 'category',
        'label'    => 'core::phrase.categories',
        'ordering' => 3,
        'to'       => '/marketplace/category/browse',
    ],
    [
        'menu'     => 'marketplace.admin',
        'name'     => 'invoices',
        'label'    => 'marketplace::phrase.invoices',
        'ordering' => 5,
        'to'       => '/marketplace/invoice/browse',
    ],
    [
        'menu'     => 'marketplace.admin',
        'name'     => 'manage_listings',
        'label'    => 'marketplace::phrase.manage_listings',
        'ordering' => 5,
        'to'       => '/marketplace/marketplace/browse',
    ],
];
