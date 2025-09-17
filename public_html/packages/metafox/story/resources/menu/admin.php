<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'story',
        'parent_name' => 'app-settings',
        'label'       => 'story::phrase.story',
        'to'          => '/story/background-set/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'story.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 0,
        'to'       => '/story/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'story.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 1,
        'to'       => '/story/permission',
    ],
    [
        'menu'     => 'story.admin',
        'name'     => 'manage',
        'label'    => 'story::phrase.manage_background_set',
        'ordering' => 1,
        'to'       => '/story/background-set/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'story.admin',
        'name'     => 'story_service',
        'label'    => 'story::phrase.story_services',
        'ordering' => 5,
        'to'       => '/story/service/browse',
    ],
];
