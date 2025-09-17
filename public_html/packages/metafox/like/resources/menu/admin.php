<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'like',
        'label'       => 'like::phrase.app_name',
        'ordering'    => 15,
        'to'          => '/like/reaction/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'like.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/like/permission',
    ],
    [
        'menu'     => 'like.admin',
        'name'     => 'manage-reactions',
        'label'    => 'like::phrase.manage_reactions',
        'ordering' => 3,
        'to'       => '/like/reaction/browse',
    ],
];
