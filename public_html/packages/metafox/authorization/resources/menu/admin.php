<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'authorization.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 1,
        'to'       => '/authorization/permission',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_role.manage'],
        ],
        'menu'     => 'authorization.admin',
        'name'     => 'roles',
        'label'    => 'authorization::phrase.roles',
        'ordering' => 2,
        'to'       => '/authorization/role/browse',
    ],
    [
        'showWhen' => ['eq', 'setting.app.env', 'local'],
        'menu'     => 'authorization.admin',
        'name'     => 'user_devices',
        'label'    => 'authorization::phrase.user_devices',
        'ordering' => 3,
        'to'       => '/authorization/device/browse',
    ],
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'members',
        'name'        => 'permissions',
        'label'       => 'core::phrase.permissions',
        'ordering'    => 5,
        'to'          => '/authorization/permission',
    ],
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_role.manage'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'members',
        'name'        => 'roles',
        'label'       => 'authorization::phrase.roles',
        'ordering'    => 6,
        'to'          => '/authorization/role/browse',
    ],
    [
        'showWhen'    => ['eq', 'setting.app.env', 'local'],
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'user_devices',
        'parent_name' => 'members',
        'label'       => 'authorization::phrase.user_devices',
        'testid'      => 'user_devices',
        'ordering'    => 10,
        'to'          => '/authorization/device/browse',
    ],
];
