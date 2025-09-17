<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'advertise.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 1,
        'to'       => '/advertise/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'advertise.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/advertise/permission',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'advertise.admin',
        'name'     => 'manage_placements',
        'label'    => 'advertise::phrase.manage_placements',
        'ordering' => 3,
        'to'       => '/advertise/placement/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'advertise.admin',
        'name'     => 'manage_advertises',
        'label'    => 'advertise::phrase.manage_advertises',
        'ordering' => 5,
        'to'       => '/advertise/advertise/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'advertise.admin',
        'name'     => 'manage_invoice',
        'label'    => 'advertise::phrase.manage_invoices',
        'ordering' => 7,
        'to'       => '/advertise/invoice/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'advertise.admin',
        'name'     => 'manage_sponsorships',
        'label'    => 'advertise::phrase.manage_sponsorships',
        'ordering' => 8,
        'to'       => 'advertise/sponsor/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'advertise.admin',
        'name'     => 'sponsor_settings',
        'label'    => 'advertise::phrase.sponsor_settings',
        'ordering' => 9,
        'to'       => '/advertise/sponsor/setting',
    ],
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'advertise',
        'label'       => 'advertise::phrase.advertise',
        'ordering'    => 1,
        'to'          => '/advertise/advertise/browse',
    ],
];
