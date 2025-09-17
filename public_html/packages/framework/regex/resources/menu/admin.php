<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'regex',
        'parent_name' => 'settings',
        'label'       => 'regex::phrase.regex_rules',
        'ordering'    => 0,
        'to'          => '/regex/setting',
    ],
    [
        'menu'     => 'regex.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 1,
        'to'       => '/regex/setting',
    ],
];
