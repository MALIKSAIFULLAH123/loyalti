<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'invite',
        'parent_name' => 'app-settings',
        'label'       => 'invite::phrase.invitation',
        'testid'      => '/invite/setting',
        'to'          => '/invite/invite/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'invite.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'to'       => '/invite/setting',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
            ['truthy', 'acl.authorization.user_permission.manage'],
        ],
        'menu'     => 'invite.admin',
        'name'     => 'permissions',
        'label'    => 'core::phrase.permissions',
        'ordering' => 2,
        'to'       => '/invite/permission',
    ],
    [
        'menu'     => 'invite.admin',
        'name'     => 'manage_invite_codes',
        'label'    => 'invite::phrase.manage_invite_codes',
        'ordering' => 2,
        'to'       => '/invite/invite-code/browse',
    ],
    [
        'menu'     => 'invite.admin',
        'name'     => 'manage_invitations',
        'label'    => 'invite::phrase.manage_invitations',
        'ordering' => 3,
        'to'       => '/invite/invite/browse',
    ],
];
