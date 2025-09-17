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
        'name'        => 'mail',
        'label'       => 'mail::phrase.mail',
        'ordering'    => 0,
        'to'          => '/mail/setting',
    ],
    [
        'menu'     => 'mail.admin',
        'name'     => 'appearance_settings',
        'label'    => 'core::phrase.appearance',
        'ordering' => 0,
        'to'       => '/mail/setting/appearance',
    ],
    [
        'menu'     => 'mail.admin',
        'name'     => 'settings',
        'label'    => 'mail::phrase.settings',
        'ordering' => 1,
        'to'       => '/mail/setting',
    ],
    [
        'menu'     => 'mail.admin',
        'name'     => 'mailer',
        'label'    => 'mail::phrase.mailers',
        'ordering' => 2,
        'to'       => '/mail/mailer/browse',
    ],
];
