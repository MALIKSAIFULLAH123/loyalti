<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'activity.admin',
        'name'     => 'settings',
        'label'    => 'activity::phrase.settings',
        'ordering' => 1,
        'to'       => '/activity/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'activity.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/activity/permission',
    ],
    [
        'menu'     => 'activity.admin',
        'name'     => 'types',
        'label'    => 'activity::phrase.types',
        'ordering' => 3,
        'to'       => '/activity/type/browse',
    ],
    [
        'menu'     => 'activity.admin',
        'name'     => 'feeds',
        'label'    => 'activity::phrase.manage_feed',
        'ordering' => 4,
        'to'       => '/activity/feed/browse',
    ],
    [
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'feed',
        'label'       => 'activity::phrase.feed',
        'ordering'    => 10,
        'to'          => '/activity/feed/browse',
    ],
];
