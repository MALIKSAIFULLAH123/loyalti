<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'photo',
        'label'       => 'photo::phrase.photo',
        'ordering'    => 20,
        'to'          => '/photo/photo/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'photo.admin',
        'name'     => 'settings',
        'label'    => 'photo::phrase.settings',
        'ordering' => 1,
        'to'       => '/photo/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'photo.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/photo/permission',
    ],
    [
        'menu'     => 'photo.admin',
        'name'     => 'category',
        'label'    => 'core::phrase.categories',
        'ordering' => 3,
        'to'       => '/photo/category/browse',
    ],
    [
        'menu'     => 'photo.admin',
        'name'     => 'manage_photos',
        'label'    => 'photo::phrase.manage_photos',
        'ordering' => 4,
        'to'       => '/photo/photo/browse',
    ],
    [
        'menu'     => 'photo.admin',
        'name'     => 'manage_albums',
        'label'    => 'photo::phrase.manage_albums',
        'ordering' => 5,
        'to'       => '/photo/photo-album/browse',
    ],
];
