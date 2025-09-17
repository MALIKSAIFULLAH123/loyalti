<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'maintenance',
        'name'        => 'report_items',
        'label'       => 'report::phrase.reports',
        'ordering'    => 3,
        'to'          => '/report/items/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'report.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 0,
        'to'       => '/report/setting',
    ],
    [
        'menu'     => 'report.admin',
        'name'     => 'report_items',
        'label'    => 'report::phrase.manage_reports',
        'ordering' => 1,
        'to'       => '/report/items/browse',
    ],
    [
        'menu'     => 'report.admin',
        'name'     => 'manage_report_reasons',
        'label'    => 'report::phrase.manage_reasons',
        'ordering' => 2,
        'to'       => '/report/reason/browse',
    ],
];
