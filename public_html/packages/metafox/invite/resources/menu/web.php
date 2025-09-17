<?php

/* this is auto generated file */
return [
    [
        'menu'     => 'core.dropdownMenu',
        'name'     => 'invite',
        'label'    => 'invite::phrase.invitations',
        'subInfo'  => 'invite::phrase.invite_friends_to_community',
        'ordering' => 4,
        'to'       => '/invite/manage',
        'icon'     => 'ico-envelope',
    ],
    [
        'menu'     => 'core.quickCreateMenu',
        'name'     => 'invite',
        'label'    => 'invite::phrase.invitation',
        'ordering' => 2,
        'to'       => '/invite',
        'icon'     => 'ico-envelope',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn'],
        ],
        'menu'     => 'core.primaryFooterMenu',
        'name'     => 'invite',
        'label'    => 'invite::phrase.invitations',
        'ordering' => 4,
        'to'       => '/invite/manage',
    ],
    [
        'tab'      => 'manage',
        'menu'     => 'invite.sidebarMenu',
        'name'     => 'invitations',
        'label'    => 'invite::phrase.all_invitations',
        'ordering' => 1,
        'icon'     => 'ico-envelope-o',
        'to'       => '/invite/manage',
    ],
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.invite.invite.create'],
        ],
        'tab'         => 'invite',
        'menu'        => 'invite.sidebarMenu',
        'name'        => 'invite',
        'label'       => 'invite::phrase.new_invite',
        'ordering'    => 2,
        'as'          => 'sidebarButton',
        'buttonProps' => [
            'fullWidth' => true,
            'color'     => 'primary',
            'variant'   => 'contained',
        ],
        'icon'        => 'ico-plus',
        'to'          => '/invite',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.is_pending'],
            ['truthy', 'item.extra.can_create'],
        ],
        'menu'     => 'invite.invite.itemActionMenu',
        'name'     => 'resend',
        'label'    => 'invite::phrase.resend',
        'ordering' => 1,
        'value'    => 'resend',
        'icon'     => 'ico-forward-o',
    ],
    [
        'showWhen' => [],
        'menu'     => 'invite.invite.itemActionMenu',
        'name'     => 'delete',
        'label'    => 'invite::phrase.delete',
        'ordering' => 2,
        'value'    => 'deleteItem',
        'icon'     => 'ico-trash-o',
    ],
];
