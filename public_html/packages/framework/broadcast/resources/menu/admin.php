<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'broadcast',
        'parent_name' => 'app-settings',
        'resolution'  => 'admin',
        'label'       => 'Broadcast',
        'testid'      => '/broadcast/setting',
        'to'          => '/broadcast/setting',
        'is_active'   => 0,
    ],
    [
        'menu'       => 'broadcast.admin',
        'name'       => 'settings',
        'resolution' => 'admin',
        'label'      => 'core::phrase.settings',
        'to'         => '/broadcast/setting',
        'is_active'  => 0,
    ],
    [
        'menu'       => 'broadcast.admin',
        'name'       => 'connections',
        'resolution' => 'admin',
        'label'      => 'broadcast::phrase.connections',
        'to'         => '/broadcast/connection/browse',
        'is_active'  => 0,
    ],
];
