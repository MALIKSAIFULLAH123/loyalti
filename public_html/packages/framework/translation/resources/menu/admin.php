<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'translation',
        'parent_name' => 'app-settings',
        'label'       => 'translation::phrase.translation',
        'to'          => '/translation/setting',
    ],
    [
        'menu'      => 'translation.admin',
        'name'      => 'settings',
        'label'     => 'core::phrase.settings',
        'to'        => '/translation/setting',
        'is_active' => 1,
    ],
    [
        'menu'     => 'translation.admin',
        'name'     => 'gateways',
        'label'    => 'translation::admin.gateways',
        'ordering' => 0,
        'to'       => '/translation/gateway/browse',
    ],
];
