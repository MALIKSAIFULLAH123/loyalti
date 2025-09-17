<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'ban',
        'parent_name' => 'maintenance',
        'label'       => 'ban::phrase.app_name',
        'ordering'    => 0,
        'to'          => '/ban/email/browse',
    ],
    [
        'menu'     => 'ban.admin',
        'name'     => 'email',
        'label'    => 'core::phrase.email',
        'to'       => '/ban/email/browse',
        'ordering' => 2,
    ],
    [
        'menu'     => 'ban.admin',
        'name'     => 'ip_address',
        'label'    => 'ban::phrase.ip_address',
        'to'       => '/ban/ip/browse',
        'ordering' => 3,
    ],
    [
        'menu'     => 'ban.admin',
        'name'     => 'word',
        'label'    => 'ban::phrase.word',
        'to'       => '/ban/word/browse',
        'ordering' => 5,
    ],
];
