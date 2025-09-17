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
        'name'        => 'friend',
        'label'       => 'friend::phrase.friend',
        'ordering'    => 12,
        'to'          => '/friend/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'friend.admin',
        'name'     => 'friend-settings',
        'label'    => 'friend::phrase.settings',
        'ordering' => 1,
        'to'       => '/friend/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'friend.admin',
        'name'     => 'friend-permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/friend/permission',
    ],
];
