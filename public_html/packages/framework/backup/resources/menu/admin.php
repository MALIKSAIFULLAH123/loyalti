<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'backup.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 1,
        'to'       => '/backup/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'backup.admin',
        'name'     => 'histories',
        'label'    => 'backup::phrase.backup_histories',
        'ordering' => 3,
        'to'       => '/backup/file/browse',
    ],
    [
        'menu'     => 'backup.admin',
        'name'     => 'create',
        'label'    => 'backup::phrase.backup_now',
        'ordering' => 2,
        'to'       => '/backup/file/wizard',
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'setting.backup.enable_backup'],
        ],
    ],
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'maintenance',
        'name'        => 'backup',
        'label'       => 'backup::phrase.backup',
        'ordering'    => 0,
        'to'          => '/backup/setting',
    ],
];
