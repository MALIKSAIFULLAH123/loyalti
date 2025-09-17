<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'chatplus.admin',
        'name'     => 'settings',
        'label'    => 'chatplus::phrase.settings',
        'ordering' => 0,
        'to'       => '/chatplus/setting',
    ],
    [
        'menu'     => 'chatplus.admin',
        'name'     => 'users',
        'label'    => 'chatplus::phrase.users',
        'ordering' => 0,
        'to'       => '/chatplus/setting/user',
    ],
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'chatplus',
        'label'       => 'chatplus::phrase.chatplus',
        'ordering'    => 5,
        'to'          => '/chatplus/setting',
    ],
];
