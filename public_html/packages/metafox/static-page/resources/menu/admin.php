<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'appearance',
        'name'        => 'static-page',
        'label'       => 'static-page::phrase.pages',
        'ordering'    => 3,
        'to'          => '/static-page/page/browse',
    ],
    [
        'showWhen'   => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'       => 'static-page.admin',
        'name'       => 'settings',
        'label'      => 'core::phrase.settings',
        'ordering'   => 0,
        'is_deleted' => true,
        'to'         => '/static-page/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'static-page.admin',
        'name'     => 'pages',
        'label'    => 'static-page::phrase.browse_pages',
        'ordering' => 0,
        'to'       => '/static-page/page/browse',
    ],
];
