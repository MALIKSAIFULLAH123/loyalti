<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'truthy',
            'item.extra.can_delete'
        ],
        'menu'     => 'chat.room.itemActionMenu',
        'name'     => 'delete',
        'label'    => 'core::phrase.delete',
        'ordering' => 1,
        'value'    => 'deleteItem',
        'style'    => 'danger',
    ],
    [
        'showWhen' => [
            'truthy',
            'item.extra.can_delete'
        ],
        'menu'     => 'chat.room.detailActionMenu',
        'name'     => 'delete',
        'label'    => 'core::phrase.delete',
        'ordering' => 1,
        'value'    => 'deleteItem',
        'style'    => 'danger',
    ],
    [
        'showWhen' => [],
        'menu'     => 'chat.message.itemActionMenu',
        'name'     => 'reply',
        'label'    => 'chat::web.reply',
        'ordering' => 1,
        'value'    => 'reply',
    ],
    [
        'showWhen' => [
            'truthy',
            'item.message'
        ],
        'menu'     => 'chat.message.itemActionMenu',
        'name'     => 'copy',
        'label'    => 'chat::web.copy',
        'ordering' => 2,
        'value'    => 'copy',
    ],
    [
        'showWhen' => [
            'truthy',
            'item.permissions.can_edit'
        ],
        'menu'     => 'chat.message.itemActionMenu',
        'name'     => 'edit',
        'label'    => 'chat::web.edit',
        'ordering' => 3,
        'value'    => 'editItem',
    ],
    [
        'showWhen' => [
            'truthy',
            'item.permissions.can_delete'
        ],
        'menu'     => 'chat.message.itemActionMenu',
        'name'     => 'delete',
        'label'    => 'core::phrase.delete',
        'ordering' => 4,
        'value'    => 'removeItem',
        'style'    => 'danger',
    ],
];
