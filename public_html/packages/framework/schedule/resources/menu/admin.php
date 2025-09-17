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
        'name'        => 'schedule',
        'label'       => 'schedule::phrase.schedule_jobs',
        'ordering'    => 0,
        'to'          => '/schedule/setting',
    ],
    [
        'menu'     => 'schedule.admin',
        'name'     => 'jobs',
        'label'    => 'schedule::phrase.schedule_jobs',
        'ordering' => 2,
        'to'       => '/schedule/job/browse',
    ],
    [
        'menu'     => 'schedule.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'to'       => '/schedule/setting',
        'ordering' => 1,
    ],
];
