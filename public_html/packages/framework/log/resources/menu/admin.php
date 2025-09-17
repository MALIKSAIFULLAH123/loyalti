<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'settings',
        'name'        => 'log',
        'label'       => 'log::phrase.log',
        'ordering'    => 0,
        'to'          => '/log/channel/browse',
    ],
    [
        'menu'     => 'log.admin',
        'name'     => 'settings',
        'label'    => 'log::phrase.settings',
        'ordering' => 1,
        'to'       => '/log/setting',
    ],
    [
        'menu'     => 'log.admin',
        'name'     => 'channels',
        'label'    => 'log::phrase.channels',
        'ordering' => 2,
        'to'       => '/log/channel/browse',
    ],
    [
        'menu'       => 'log.admin',
        'name'       => 'add_new_channel',
        'label'      => 'log::phrase.add_new_channel',
        'ordering'   => 3,
        'icon'       => 'ico-plus',
        'to'         => '/log/channel/create',
        'is_deleted' => true,
    ],
    [
        'menu'     => 'log.admin',
        'name'     => 'databases',
        'label'    => 'log::phrase.databases',
        'ordering' => 4,
        'to'       => '/log/db/browse/msg',
    ],
    [
        'menu'     => 'log.admin',
        'name'     => 'file',
        'label'    => 'log::phrase.files',
        'ordering' => 5,
        'to'       => '/log/file/browse',
    ],
];
