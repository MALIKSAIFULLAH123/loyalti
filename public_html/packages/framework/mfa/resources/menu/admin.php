<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'mfa',
        'parent_name' => 'settings',
        'label'       => 'mfa::phrase.multi_factor_authentication',
        'ordering'    => 0,
        'to'          => '/mfa/setting',
    ],
    [
        'menu'     => 'mfa.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 1,
        'to'       => '/mfa/setting',
    ],
];
