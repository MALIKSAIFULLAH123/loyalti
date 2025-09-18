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
        'name'        => 'saved',
        'label'       => 'saved::phrase.saved_items',
        'ordering'    => 23,
        'to'          => '/saved/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'saved.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 0,
        'to'       => '/saved/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'saved.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 1,
        'to'       => '/saved/permission',
    ],
];
