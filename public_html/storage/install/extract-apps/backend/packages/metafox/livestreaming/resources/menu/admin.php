<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'livestreaming',
        'label'       => 'livestreaming::phrase.live_video',
        'ordering'    => 27,
        'to'          => '/livestreaming/live-video/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'livestreaming.admin',
        'name'     => 'settings',
        'label'    => 'livestreaming::phrase.settings',
        'ordering' => 0,
        'to'       => '/livestreaming/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'livestreaming.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 1,
        'to'       => '/livestreaming/permission',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'livestreaming.admin',
        'name'     => 'streaming_service',
        'label'    => 'livestreaming::phrase.streaming_service',
        'ordering' => 2,
        'to'       => '/livestreaming/streaming-service/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'livestreaming.admin',
        'name'     => 'firebase_setting',
        'label'    => 'livestreaming::phrase.firebase_settings',
        'ordering' => 3,
        'to'       => '/firebase/setting',
    ],
    [
        'menu'     => 'livestreaming.admin',
        'name'     => 'manage_live_videos',
        'label'    => 'livestreaming::phrase.manage_live_videos',
        'ordering' => 4,
        'to'       => '/livestreaming/live-video/browse',
    ],
    //    [
    //        'menu'     => 'livestreaming.admin',
    //        'name'     => 'firebase_service_account',
    //        'label'    => 'livestreaming::phrase.firebase_service_account',
    //        'ordering' => 2,
    //        'to'       => '/livestreaming/setting/service-account',
    //    ],
];
