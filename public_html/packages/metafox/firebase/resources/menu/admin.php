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
        'name'        => 'firebase_setting',
        'label'       => 'firebase::phrase.firebase_label',
        'ordering'    => 0,
        'to'          => '/firebase/setting',
    ],
    [
        'menu'     => 'firebase.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 1,
        'to'       => '/firebase/setting',
    ],
    [
        'menu'       => 'firebase.admin',
        'name'       => 'manage_device',
        'label'      => 'firebase::phrase.manage_devices',
        'to'         => '/firebase/device/browse',
        'is_active'  => 0,
        'is_deleted' => 1,
        'ordering'   => 2,
    ],
    [
        'menu'     => 'firebase.admin',
        'name'     => 'upload_config',
        'label'    => 'firebase::phrase.import_settings',
        'ordering' => 3,
        'to'       => '/firebase/setting/create',
    ],
];
