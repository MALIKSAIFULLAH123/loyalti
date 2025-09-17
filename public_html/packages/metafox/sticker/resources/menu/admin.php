<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'sticker',
        'label'       => 'sticker::phrase.sticker',
        'ordering'    => 23,
        'to'          => '/sticker/sticker-set/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'sticker.admin',
        'name'     => 'settings',
        'label'    => 'sticker::phrase.settings',
        'ordering' => 1,
        'to'       => '/sticker/setting',
    ],
    [
        'menu'     => 'sticker.admin',
        'name'     => 'manage-sticker',
        'label'    => 'sticker::phrase.manage_sticker',
        'ordering' => 2,
        'to'       => '/sticker/sticker-set/browse',
    ],
];
