<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'getting-started',
        'parent_name' => 'app-settings',
        'label'       => 'getting-started::phrase.getting_started',
        'testid'      => '/getting-started/setting',
        'to'          => '/getting-started/todo-list/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'getting-started.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'to'       => '/getting-started/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'getting-started.admin',
        'name'     => 'manage_todo_list',
        'label'    => 'getting-started::phrase.manage_todo_list',
        'ordering' => 5,
        'to'       => '/getting-started/todo-list/browse',
    ],
];
