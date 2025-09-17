<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'appearance',
        'name'        => 'menus',
        'label'       => 'menu::phrase.menus',
        'ordering'    => 2,
        'to'          => '/menu/menu/browse',
    ],
    [
        'showWhen'  => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'      => 'menu.admin',
        'name'      => 'settings',
        'label'     => 'menu::phrase.settings',
        'ordering'  => 1,
        'to'        => '/menu/setting',
        'is_active' => 0,
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'menu.admin',
        'name'     => 'menus',
        'label'    => 'menu::phrase.menus',
        'ordering' => 2,
        'to'       => '/menu/menu/browse',
    ],
    [
        'showWhen'   => ['eq', 'settings.app.env', 'local'],
        'menu'       => 'menu.admin',
        'name'       => 'add_new_menu',
        'label'      => 'menu::phrase.add_new_menu',
        'ordering'   => 3,
        'to'         => '/menu/menu/create',
        'is_deleted' => 1,
    ],
];
