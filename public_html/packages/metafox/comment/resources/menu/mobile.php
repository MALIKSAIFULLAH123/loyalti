<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.is_pending'],
            ['truthy', 'item.extra.can_approve'],
        ],
        'menu'     => 'comment.comment.itemActionMenu',
        'name'     => 'approve',
        'label'    => 'core::phrase.approve',
        'ordering' => 1,
        'value'    => 'approveItem',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_edit'],
        ],
        'menu'     => 'comment.comment.itemActionMenu',
        'name'     => 'edit',
        'label'    => 'comment::phrase.edit',
        'ordering' => 1,
        'value'    => 'editComment',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_view_histories'],
        ],
        'menu'     => 'comment.comment.itemActionMenu',
        'name'     => 'view_history',
        'label'    => 'comment::phrase.view_edit_history',
        'ordering' => 2,
        'value'    => 'comment/showHistory',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_hide_global'],
            ['falsy', 'item.is_hidden'],
        ],
        'menu'     => 'comment.comment.itemActionMenu',
        'name'     => 'hide_global',
        'label'    => 'comment::phrase.hide_comment',
        'ordering' => 3,
        'value'    => 'comment/hideGlobalItem',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_hide_global'],
            ['truthy', 'item.is_hidden'],
        ],
        'menu'     => 'comment.comment.itemActionMenu',
        'name'     => 'unhide_global',
        'label'    => 'comment::phrase.unhide_comment',
        'ordering' => 3,
        'value'    => 'comment/unhideGlobalItem',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_hide'],
            ['falsy', 'item.is_hidden'],
        ],
        'menu'     => 'comment.comment.itemActionMenu',
        'name'     => 'hide',
        'label'    => 'core::web.hide',
        'ordering' => 4,
        'value'    => 'comment/hideItem',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_hide'],
            ['truthy', 'item.is_hidden'],
        ],
        'menu'     => 'comment.comment.itemActionMenu',
        'name'     => 'unhide',
        'label'    => 'comment::phrase.unhide_comment',
        'ordering' => 4,
        'value'    => 'comment/unhideItem',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_delete'],
        ],
        'className' => 'itemDelete',
        'style'     => 'danger',
        'menu'      => 'comment.comment.itemActionMenu',
        'name'      => 'delete',
        'label'     => 'comment::phrase.delete',
        'ordering'  => 5,
        'value'     => 'deleteComment',
    ],
];
