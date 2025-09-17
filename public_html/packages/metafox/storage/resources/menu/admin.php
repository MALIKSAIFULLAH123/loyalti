<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'settings',
        'name'        => 'storage',
        'label'       => 'storage::phrase.storage',
        'ordering'    => 0,
        'to'          => '/storage/setting',
    ],
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'appearance',
        'name'        => 'assets',
        'label'       => 'storage::phrase.assets',
        'ordering'    => 5,
        'to'          => '/storage/asset/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'storage.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 0,
        'to'       => '/storage/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'storage.admin',
        'name'     => 'storage',
        'label'    => 'storage::phrase.storages',
        'ordering' => 1,
        'to'       => '/storage/disk/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'storage.admin',
        'name'     => 'disks',
        'label'    => 'storage::phrase.assets',
        'ordering' => 4,
        'to'       => '/storage/asset/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'storage.admin',
        'name'     => 'configurations',
        'label'    => 'storage::phrase.configurations',
        'ordering' => 2,
        'to'       => '/storage/option/browse',
    ],
];
