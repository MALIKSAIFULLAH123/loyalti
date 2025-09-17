<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'settings',
        'name'        => 'queue',
        'label'       => 'queue::phrase.message_queue',
        'ordering'    => 0,
        'to'          => '/queue/setting',
    ],
    [
        'menu'     => 'queue.admin',
        'name'     => 'settings',
        'label'    => 'queue::phrase.settings',
        'ordering' => 1,
        'to'       => '/queue/setting',
    ],
    [
        'menu'     => 'queue.admin',
        'name'     => 'connections',
        'label'    => 'queue::phrase.connections',
        'ordering' => 2,
        'to'       => '/queue/connection/browse',
    ],
    [
        'menu'     => 'queue.admin',
        'name'     => 'failed_jobs',
        'label'    => 'queue::phrase.failed_jobs',
        'ordering' => 3,
        'to'       => '/queue/failed-job/browse',
    ],
];
