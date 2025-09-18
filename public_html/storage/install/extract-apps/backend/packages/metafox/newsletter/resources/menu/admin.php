<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'newsletter',
        'parent_name' => 'app-settings',
        'label'       => 'newsletter::phrase.newsletter',
        'to'          => '/newsletter/newsletter/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'newsletter.admin',
        'name'     => 'manage_newsletter',
        'label'    => 'newsletter::phrase.manage_newsletters',
        'ordering' => 1,
        'as'       => 'subMenu',
        'to'       => '/newsletter/newsletter/browse',
    ],
];
