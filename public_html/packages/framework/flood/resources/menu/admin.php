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
        'name'        => 'flood-control',
        'label'       => 'flood::phrase.flood_control',
        'ordering'    => 0,
        'to'          => '/flood/setting',
    ],
    [
        'menu'     => 'flood.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 1,
        'to'       => '/flood/setting',
    ],
    [
        'menu'     => 'flood.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/flood/permission',
    ],
];
