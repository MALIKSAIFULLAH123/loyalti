<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'event',
        'label'       => 'event::phrase.event',
        'ordering'    => 8,
        'to'          => '/event/event/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'event.admin',
        'name'     => 'settings',
        'label'    => 'event::phrase.settings',
        'ordering' => 1,
        'to'       => '/event/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'event.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/event/permission',
    ],
    [
        'menu'     => 'event.admin',
        'name'     => 'category',
        'label'    => 'core::phrase.categories',
        'ordering' => 3,
        'to'       => '/event/category/browse',
    ],
    [
        'menu'     => 'event.admin',
        'name'     => 'manage_events',
        'label'    => 'event::phrase.manage_events',
        'ordering' => 4,
        'to'       => '/event/event/browse',
    ],
];
