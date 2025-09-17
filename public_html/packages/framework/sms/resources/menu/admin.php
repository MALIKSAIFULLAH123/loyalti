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
        'name'        => 'sms',
        'label'       => 'sms::phrase.sms',
        'ordering'    => 0,
        'to'          => '/sms/setting',
    ],
    [
        [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'sms.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 1,
        'to'       => '/sms/setting',
    ],
    [
        [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'sms.admin',
        'name'     => 'services',
        'label'    => 'sms::phrase.services',
        'ordering' => 2,
        'to'       => '/sms/service/browse',
    ],
];
