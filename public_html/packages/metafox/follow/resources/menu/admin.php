<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'follow',
        'parent_name' => 'app-settings',
        'label'       => 'Follow',
        'testid'      => '/follow/setting',
        'to'          => '/follow/setting',
        'is_active'   => 0,
    ],
    [
        'showWhen'  => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'      => 'follow.admin',
        'name'      => 'settings',
        'label'     => 'core::phrase.settings',
        'to'        => '/follow/setting',
        'is_active' => 0,
    ],
    [
        'showWhen'  => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'      => 'follow.admin',
        'name'      => 'permissions',
        'label'     => 'core::phrase.permissions',
        'to'        => '/follow/permission',
        'is_active' => 0,
    ],
];
