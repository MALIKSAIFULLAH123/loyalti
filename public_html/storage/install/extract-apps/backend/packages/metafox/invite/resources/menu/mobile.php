<?php

/* this is auto generated file */
return [
    [
        'showWhen'  => [],
        'menu'      => 'core.helperMenu',
        'name'      => 'invite',
        'label'     => 'invite::phrase.invitations',
        'ordering'  => 2,
        'value'     => '',
        'to'        => '/invite/manage',
        'as'        => 'helper',
        'icon'      => 'envelope',
        'iconColor' => '#686868',
    ],
    [
        'tab'      => 'manage',
        'menu'     => 'invite.sidebarMenu',
        'name'     => 'invitations',
        'label'    => 'invite::phrase.all_invitations',
        'ordering' => 1,
        'icon'     => 'envelope-o',
        'to'       => '/invite/manage',
        'value'    => 'viewAll',
        'params'   => [
            'module_name'   => 'invite',
            'resource_name' => 'invite',
        ],
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
        'icon'     => 'ico-warning-o',
    ],
    [
        'showWhen' => [],
        'menu'     => 'invite.invite.itemActionMenu',
        'name'     => 'delete',
        'label'    => 'invite::phrase.delete',
        'ordering' => 2,
        'value'    => 'deleteItem',
        'style'    => 'danger',
        'icon'     => 'ico-trash',
    ],
];
