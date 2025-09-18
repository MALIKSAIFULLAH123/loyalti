<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'poll',
        'label'       => 'poll::phrase.poll',
        'ordering'    => 21,
        'to'          => '/poll/poll/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'poll.admin',
        'name'     => 'settings',
        'label'    => 'poll::phrase.settings',
        'ordering' => 1,
        'to'       => '/poll/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'poll.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/poll/permission',
    ],
    [
        'menu'     => 'poll.admin',
        'name'     => 'manage_polls',
        'label'    => 'poll::phrase.manage_polls',
        'ordering' => 3,
        'to'       => '/poll/poll/browse',
    ],
];
