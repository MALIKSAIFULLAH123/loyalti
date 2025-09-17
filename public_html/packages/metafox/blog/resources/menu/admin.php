<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'blog.admin',
        'name'     => 'settings',
        'label'    => 'blog::phrase.settings',
        'ordering' => 1,
        'to'       => '/blog/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'blog.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/blog/permission',
    ],
    [
        'menu'     => 'blog.admin',
        'name'     => 'categories',
        'label'    => 'core::phrase.categories',
        'ordering' => 3,
        'to'       => '/blog/category/browse',
    ],
    [
        'menu'     => 'blog.admin',
        'name'     => 'manage_blogs',
        'label'    => 'blog::phrase.manage_blogs',
        'ordering' => 4,
        'to'       => '/blog/blog/browse',
    ],
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'blog',
        'label'       => 'blog::phrase.blog',
        'ordering'    => 4,
        'to'          => '/blog/blog/browse',
    ],
];
