<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'video',
        'label'       => 'video::phrase.video',
        'ordering'    => 26,
        'to'          => '/video/video/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'video.admin',
        'name'     => 'settings',
        'label'    => 'video::phrase.settings',
        'ordering' => 1,
        'to'       => '/video/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'video.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/video/permission',
    ],
    [
        'menu'     => 'video.admin',
        'name'     => 'category',
        'label'    => 'core::phrase.categories',
        'ordering' => 3,
        'to'       => '/video/category/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'video.admin',
        'name'     => 'video_service',
        'label'    => 'video::phrase.video_services',
        'ordering' => 5,
        'to'       => '/video/service/browse',
    ],
    [
        'menu'     => 'video.admin',
        'name'     => 'manage_videos',
        'label'    => 'video::phrase.manage_videos',
        'ordering' => 4,
        'to'       => '/video/video/browse',
    ],
];
