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
        'name'        => 'attachments',
        'label'       => 'core::phrase.attachments',
        'ordering'    => 6,
        'to'          => '/attachment/type/browse',
    ],
    [
        'menu'     => 'attachment.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'to'       => '/attachment/setting',
        'ordering' => 1,
    ],
    [
        'menu'     => 'attachment.admin',
        'name'     => 'attachment_types',
        'label'    => 'attachment::phrase.types',
        'to'       => '/attachment/type/browse',
        'ordering' => 2,
    ],
];
