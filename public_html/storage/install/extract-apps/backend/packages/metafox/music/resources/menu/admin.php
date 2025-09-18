<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'music',
        'label'       => 'music::phrase.music',
        'ordering'    => 26,
        'to'          => '/music/music-song/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'music.admin',
        'name'     => 'settings',
        'label'    => 'music::phrase.settings',
        'ordering' => 1,
        'to'       => '/music/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'music.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/music/permission',
    ],
    [
        'menu'     => 'music.admin',
        'name'     => 'genres',
        'label'    => 'core::phrase.genres',
        'ordering' => 3,
        'to'       => '/music/genre/browse',
    ],
    [
        'menu'     => 'music.admin',
        'name'     => 'manage_songs',
        'label'    => 'music::phrase.manage_songs',
        'ordering' => 4,
        'to'       => '/music/music-song/browse',
    ],
    [
        'menu'     => 'music.admin',
        'name'     => 'manage_albums',
        'label'    => 'music::phrase.manage_albums',
        'ordering' => 5,
        'to'       => '/music/music-album/browse',
    ],
    [
        'menu'     => 'music.admin',
        'name'     => 'manage_playlists',
        'label'    => 'music::phrase.manage_playlists',
        'ordering' => 6,
        'to'       => '/music/music-playlist/browse',
    ],
];
