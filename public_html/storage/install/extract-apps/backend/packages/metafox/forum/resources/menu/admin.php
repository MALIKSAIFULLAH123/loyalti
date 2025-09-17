<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'forum',
        'label'       => 'forum::phrase.forum',
        'ordering'    => 11,
        'to'          => '/forum/forum-thread/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'forum.admin',
        'name'     => 'setting',
        'label'    => 'core::phrase.settings',
        'ordering' => 1,
        'to'       => '/forum/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'forum.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/forum/permission',
    ],
    [
        'menu'     => 'forum.admin',
        'name'     => 'manage_forum',
        'label'    => 'forum::phrase.manage_forums',
        'ordering' => 3,
        'to'       => '/forum/forum/browse',
    ],
    [
        'menu'     => 'forum.admin',
        'name'     => 'manage_threads',
        'label'    => 'forum::phrase.manage_threads',
        'ordering' => 4,
        'to'       => '/forum/forum-thread/browse',
    ],
    [
        'menu'     => 'forum.admin',
        'name'     => 'manage_posts',
        'label'    => 'forum::phrase.manage_posts',
        'ordering' => 5,
        'to'       => '/forum/forum-post/browse',
    ],
];
