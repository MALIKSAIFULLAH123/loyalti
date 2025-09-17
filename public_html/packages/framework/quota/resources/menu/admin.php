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
        'name'        => 'quota-control',
        'label'       => 'quota::phrase.quota_control',
        'ordering'    => 0,
        'to'          => '/quota/setting',
    ],
    [
        'menu'     => 'quota.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 1,
        'to'       => '/quota/setting',
    ],
    [
        'menu'     => 'quota.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/quota/permission',
    ],
];
