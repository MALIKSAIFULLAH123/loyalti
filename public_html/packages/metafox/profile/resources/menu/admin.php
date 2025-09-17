<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'members',
        'name'        => 'custom',
        'label'       => 'profile::phrase.custom_fields',
        'ordering'    => 4,
        'as'          => 'subMenu',
        'to'          => '/profile/field/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'profile.admin',
        'name'     => 'customFields',
        'label'    => 'profile::phrase.manage_custom_fields',
        'ordering' => 6,
        'as'       => 'subMenu',
        'to'       => '/profile/field/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'profile.admin',
        'name'     => 'customBasicInfoFields',
        'label'    => 'profile::phrase.manage_basic_info_fields',
        'ordering' => 6,
        'as'       => 'subMenu',
        'to'       => '/profile/field-basic-info/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'profile.admin',
        'name'     => 'customSection',
        'label'    => 'profile::phrase.manage_profile_sections',
        'ordering' => 8,
        'to'       => '/profile/section/browse',
    ],
    [
        'showWhen'  => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'      => 'profile.admin',
        'name'      => 'customProfiles',
        'label'     => 'profile::phrase.custom_profiles',
        'ordering'  => 10,
        'to'        => '/profile/profile/browse',
        'is_active' => 0,
    ],
    [
        'menu'      => 'profile.admin',
        'name'      => 'createProfile',
        'label'     => 'profile::phrase.add_custom_profile',
        'ordering'  => 11,
        'to'        => '/profile/profile/create',
        'is_active' => 0,
    ],
];
