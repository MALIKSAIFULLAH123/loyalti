<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'      => 'chat.admin',
        'name'      => 'settings',
        'label'     => 'core::phrase.settings',
        'ordering'  => 0,
        'to'        => '/chat/setting',
        'is_active' => 1,
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'      => 'chat.admin',
        'name'      => 'permissions',
        'label'     => 'core::phrase.permissions',
        'ordering'  => 1,
        'to'        => '/chat/permission',
        'is_active' => 1,
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'parent_name' => 'app-settings',
        'name'        => 'chat',
        'label'       => 'chat::phrase.chat',
        'ordering'    => 0,
        'to'          => '/chat/setting',
        'is_active'   => 1,
    ],
    [
        'menu'     => 'chat.admin',
        'name'     => 'migrate_to_chat_plus',
        'label'    => 'chat::phrase.migrate_to_chatplus',
        'ordering' => 3,
        'to'       => '/chat/setting/migrate-to-chatplus',
        'showWhen' => [
            'and',
            ['truthy', 'setting.chat.is_active'],
        ],
        'is_active'  => 0, // Temporarily disable this menu
        'is_deleted' => 1,
    ],
];
