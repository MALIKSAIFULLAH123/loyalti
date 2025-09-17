<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'announcement.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/announcement/permission',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'announcement.admin',
        'name'     => 'manage_announcement',
        'label'    => 'announcement::phrase.manage_announcements',
        'ordering' => 3,
        'to'       => '/announcement/announcement/browse',
    ],
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'announcement',
        'label'       => 'announcement::phrase.announcement',
        'ordering'    => 2,
        'to'          => '/announcement/announcement/browse',
    ],
];
