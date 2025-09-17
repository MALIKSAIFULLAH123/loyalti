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
        'name'        => 'seo',
        'label'       => 'seo::phrase.seo',
        'ordering'    => 0,
        'to'          => '/seo/meta/browse',
    ],
    [
        'menu'     => 'seo.admin',
        'name'     => 'settings',
        'label'    => 'seo::phrase.settings',
        'ordering' => 0,
        'to'       => '/seo/meta/browse',
    ],
    [
        'menu'     => 'seo.admin',
        'name'     => 'sitemap_settings',
        'label'    => 'seo::phrase.sitemap_settings',
        'ordering' => 0,
        'to'       => '/seo/setting',
    ],
];
