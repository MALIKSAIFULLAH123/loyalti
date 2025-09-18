<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'group',
        'label'       => 'group::phrase.group',
        'ordering'    => 14,
        'to'          => '/group/group/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'group.admin',
        'name'     => 'setting',
        'label'    => 'core::phrase.settings',
        'ordering' => 1,
        'to'       => '/group/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'group.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/group/permission',
    ],
    [
        'menu'     => 'group.admin',
        'name'     => 'example_rules',
        'label'    => 'group::phrase.manage_example_group_rules',
        'ordering' => 3,
        'to'       => '/group/example-rule/browse',
    ],
    [
        'menu'     => 'group.admin',
        'name'     => 'category',
        'label'    => 'core::phrase.categories',
        'ordering' => 5,
        'to'       => '/group/category/browse',
    ],
    [
        'menu'     => 'group.admin',
        'name'     => 'manage_groups',
        'label'    => 'group::phrase.manage_groups',
        'ordering' => 6,
        'to'       => '/group/group/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'group.admin',
        'name'     => 'customFields',
        'label'    => 'profile::phrase.manage_custom_fields',
        'ordering' => 7,
        'to'       => '/group/field/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'group.admin',
        'name'     => 'customSection',
        'label'    => 'profile::phrase.manage_custom_sections',
        'ordering' => 9,
        'to'       => '/group/section/browse',
    ],
];
