<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'contact',
        'parent_name' => 'app-settings',
        'label'       => 'contact::phrase.contact',
        'testid'      => '/contact/setting',
        'to'          => '/contact/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'contact.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'to'       => '/contact/setting',
        'ordering' => 1,
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'contact.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'to'       => '/contact/permission',
        'ordering' => 2,
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'contact.admin',
        'name'     => 'categories',
        'label'    => 'core::phrase.categories',
        'ordering' => 3,
        'to'       => '/contact/category/browse',
    ],
];
