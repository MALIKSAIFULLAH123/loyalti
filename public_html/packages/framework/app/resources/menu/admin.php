<?php

/* this is auto generated file */
return [
    [
        'showWhen'   => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'       => 'app.admin',
        'name'       => 'browse',
        'label'      => 'core::phrase.browse',
        'ordering'   => 1,
        'to'         => '/app/package/browse',
        'is_deleted' => 1,
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'app.admin',
        'name'     => 'installed',
        'label'    => 'app::phrase.installed',
        'ordering' => 1,
        'to'       => '/app/package/browse/installed',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'app.admin',
        'name'     => 'uploaded',
        'label'    => 'app::phrase.uploaded',
        'ordering' => 2,
        'to'       => '/app/package/browse/uploaded',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'app.admin',
        'name'     => 'upgrade',
        'label'    => 'app::phrase.upgrade',
        'ordering' => 2,
        'to'       => '/app/upgrade',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'app.admin',
        'name'     => 'purchase_history',
        'label'    => 'core::phrase.purchased',
        'ordering' => 3,
        'to'       => '/app/package/browse/purchased',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'app.admin',
        'name'     => 'find_more',
        'label'    => 'app::phrase.find_more',
        'ordering' => 4,
        'to'       => '/app/store/products/browse',
    ],
    [
        'showWhen' => ['eq', 'setting.app.env', 'local'],
        'menu'     => 'app.admin',
        'name'     => 'import_app',
        'label'    => 'app::phrase.import_app',
        'ordering' => 4,
        'to'       => '/app/package/form-import',
    ],
    [
        'showWhen' => ['eq', 'setting.app.env', 'local'],
        'menu'     => 'app.admin',
        'name'     => 'add_app',
        'label'    => 'app::phrase.new_app',
        'ordering' => 5,
        'to'       => '/app/package/create',
    ],
    [
        'showWhen' => ['eq', 'setting.app.env', 'local'],
        'menu'     => 'app.admin',
        'name'     => 'add_language',
        'label'    => 'app::phrase.new_language',
        'ordering' => 6,
        'to'       => '/app/package/form-create-language',
    ],
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'apps',
        'name'        => 'installed',
        'label'       => 'app::phrase.installed',
        'ordering'    => 1,
        'to'          => '/app/package/browse/installed',
    ],
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'apps',
        'name'        => 'uploaded',
        'label'       => 'app::phrase.uploaded',
        'ordering'    => 2,
        'to'          => '/app/package/browse/uploaded',
    ],
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'apps',
        'name'        => 'purchase_history',
        'label'       => 'core::phrase.purchased',
        'ordering'    => 3,
        'to'          => '/app/package/browse/purchased',
    ],
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'apps',
        'name'        => 'more',
        'label'       => 'app::phrase.find_more',
        'ordering'    => 4,
        'to'          => '/app/store/products/browse',
    ],
];
