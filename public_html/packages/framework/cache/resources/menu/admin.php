<?php

/* this is auto generated file */
return [
    [
        'menu'     => 'cache.admin',
        'name'     => 'settings',
        'label'    => 'cache::phrase.settings',
        'ordering' => 1,
        'to'       => '/cache/setting',
    ],
    [
        'menu'     => 'cache.admin',
        'name'     => 'browse',
        'label'    => 'cache::phrase.cache_storages',
        'ordering' => 2,
        'to'       => '/cache/store/browse',
    ],
    [
        'menu'       => 'cache.admin',
        'name'       => 'add_store',
        'label'      => 'cache::phrase.add_new_cache',
        'ordering'   => 3,
        'to'         => '/cache/store/create',
        'is_deleted' => 1,
    ],
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'settings',
        'name'        => 'cache',
        'label'       => 'cache::phrase.cache',
        'ordering'    => 0,
        'to'          => '/cache/setting',
    ],
];
