<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'tourguide',
        'parent_name' => 'app-settings',
        'label'       => 'tourguide::phrase.tour_guide',
        'to'          => '/tourguide/permission',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'tourguide.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'to'       => '/tourguide/permission',
        'ordering' => 1,
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'tourguide.admin',
        'name'     => 'position',
        'label'    => 'tourguide::phrase.positioning',
        'to'       => '/tourguide/setting/position',
        'ordering' => 2,
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'tourguide.admin',
        'name'     => 'manage_tourguides',
        'label'    => 'tourguide::phrase.manage_guides',
        'ordering' => 3,
        'to'       => '/tourguide/tour-guide/browse',
    ],
];
