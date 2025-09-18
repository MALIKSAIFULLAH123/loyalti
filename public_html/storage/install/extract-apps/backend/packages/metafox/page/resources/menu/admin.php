<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'page_app',
        'label'       => 'page::phrase.page',
        'ordering'    => 18,
        'to'          => '/page/page/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'page.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 1,
        'to'       => '/page/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'page.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/page/permission',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'page.admin',
        'name'     => 'manage_claims',
        'label'    => 'page::phrase.manage_claims',
        'ordering' => 2,
        'to'       => '/page/claim/browse',
    ],
    [
        'menu'     => 'page.admin',
        'name'     => 'category',
        'label'    => 'core::phrase.categories',
        'ordering' => 3,
        'to'       => '/page/category/browse',
    ],
    [
        'menu'     => 'page.admin',
        'name'     => 'manage_pages',
        'label'    => 'page::phrase.manage_pages',
        'ordering' => 4,
        'to'       => '/page/page/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'page.admin',
        'name'     => 'customFields',
        'label'    => 'profile::phrase.manage_custom_fields',
        'ordering' => 6,
        'to'       => '/page/field/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'page.admin',
        'name'     => 'customSection',
        'label'    => 'profile::phrase.manage_custom_sections',
        'ordering' => 8,
        'to'       => '/page/section/browse',
    ],
];
