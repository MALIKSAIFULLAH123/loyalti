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
        'name'        => 'theme',
        'label'       => 'core::phrase.themes',
        'ordering'    => 0,
        'to'          => '/layout/theme/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'layout.admin',
        'name'     => 'themes',
        'label'    => 'core::phrase.themes',
        'ordering' => 0,
        'to'       => '/layout/theme/browse',
    ],
    [
        'showWhen'  => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'      => 'layout.admin',
        'name'      => 'settings',
        'label'     => 'layout::phrase.customization',
        'ordering'  => 0,
        'to'        => '/layout/snippet/browse',
        'is_active' => 0,
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'layout.admin',
        'name'     => 'build_history',
        'label'    => 'layout::phrase.rebuild_site',
        'ordering' => 0,
        'to'       => '/layout/build/wizard',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'layout.admin',
        'name'     => 'rebuild',
        'label'    => 'layout::phrase.rebuild_history',
        'ordering' => 2,
        'to'       => '/layout/build/browse',
    ],
];
