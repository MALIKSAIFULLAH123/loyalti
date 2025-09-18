<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'socialite',
        'label'       => 'socialite::phrase.socialite',
        'ordering'    => 9,
        'to'          => '/socialite/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'socialite.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 0,
        'to'       => '/socialite/setting',
    ],
    [
        'showWhen'   => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'       => 'socialite.admin',
        'name'       => 'permissions',
        'label'      => 'core::phrase.permissions',
        'ordering'   => 0,
        'to'         => '/socialite/permission',
        'is_deleted' => true,
    ],
    [
        'menu'     => 'socialite.admin',
        'name'     => 'facebook',
        'label'    => 'socialite::facebook.facebook',
        'ordering' => 1,
        'to'       => '/socialite/setting/facebook',
    ],
    [
        'menu'       => 'socialite.admin',
        'name'       => 'twitter',
        'label'      => 'Twitter',
        'ordering'   => 2,
        'to'         => '/socialite/setting/twitter',
        'is_deleted' => true,
    ],
    [
        'menu'     => 'socialite.admin',
        'name'     => 'google',
        'label'    => 'socialite::google.google',
        'ordering' => 3,
        'to'       => '/socialite/setting/google',
    ],
    [
        'menu'     => 'socialite.admin',
        'name'     => 'apple',
        'label'    => 'socialite::apple.apple',
        'ordering' => 4,
        'to'       => '/socialite/setting/apple',
    ],
    [
        'menu'     => 'socialite.admin',
        'name'     => 'tiktok',
        'label'    => 'socialite::tiktok.tiktok',
        'ordering' => 5,
        'to'       => '/socialite/setting/tiktok',
    ],
];
