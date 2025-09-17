<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'comment.admin',
        'name'     => 'settings',
        'label'    => 'comment::phrase.settings',
        'ordering' => 1,
        'to'       => '/comment/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'comment.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/comment/permission',
    ],
    [
        'menu'     => 'comment.admin',
        'name'     => 'pending',
        'label'    => 'comment::phrase.pending_comments',
        'ordering' => 3,
        'to'       => '/comment/pending/browse',
    ],
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'comment-settings',
        'label'       => 'comment::phrase.comment',
        'ordering'    => 6,
        'to'          => '/comment/pending/browse',
    ],
];
