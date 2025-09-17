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
        'name'        => 'block_words',
        'label'       => 'word::phrase.word_filters',
        'ordering'    => 0,
        'to'          => '/word/block/browse',
    ],
    [
        'showWhen'   => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'       => 'word.admin',
        'name'       => 'settings',
        'label'      => 'word::phrase.settings',
        'ordering'   => 0,
        'is_active'  => 0,
        'is_deleted' => 1,
        'to'         => '/word/setting',
    ],
    [
        'menu'     => 'word.admin',
        'name'     => 'manage',
        'label'    => 'word::phrase.preserved_words',
        'ordering' => 1,
        'to'       => '/word/block/browse',
    ],
];
