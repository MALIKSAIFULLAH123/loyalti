<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'maintenance',
        'name'        => 'search',
        'label'       => 'search::phrase.reindexing',
        'ordering'    => 2,
        'to'          => '/search/reindex/create',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'search.admin',
        'name'     => 'reindex',
        'label'    => 'search::phrase.reindexing',
        'ordering' => 1,
        'to'       => '/search/reindex/create',
    ],
];
