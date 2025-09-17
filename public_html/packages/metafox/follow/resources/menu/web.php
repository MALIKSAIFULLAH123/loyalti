<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_follow'],
        ],
        'menu'     => 'group.group.profileActionMenu',
        'name'     => 'follow',
        'label'    => 'group::phrase.follow_group',
        'ordering' => 3,
        'value'    => 'group/follow',
        'icon'     => 'ico-user3-check-o',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_unfollow'],
        ],
        'menu'     => 'group.group.profileActionMenu',
        'name'     => 'unfollow',
        'label'    => 'group::phrase.unfollow_group',
        'ordering' => 3,
        'value'    => 'group/unfollow',
        'icon'     => 'ico-user3-check-o',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_follow'],
        ],
        'menu'     => 'page.page.profileActionMenu',
        'name'     => 'follow',
        'label'    => 'page::phrase.follow_page',
        'ordering' => 2,
        'value'    => 'page/follow',
        'icon'     => 'ico-user3-check-o',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_unfollow'],
        ],
        'menu'     => 'page.page.profileActionMenu',
        'name'     => 'unfollow',
        'label'    => 'page::phrase.unfollow_page',
        'ordering' => 2,
        'value'    => 'page/unfollow',
        'icon'     => 'ico-user3-check-o',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_follow'],
        ],
        'menu'     => 'user.user.profileActionMenu',
        'name'     => 'follow',
        'label'    => 'user::phrase.follow_user',
        'ordering' => 4,
        'value'    => 'user/follow',
        'icon'     => 'ico-user2-plus-o',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_unfollow'],
        ],
        'menu'     => 'user.user.profileActionMenu',
        'name'     => 'unfollow',
        'label'    => 'user::phrase.unfollow_user',
        'ordering' => 4,
        'value'    => 'user/unfollow',
        'icon'     => 'ico-user2-minus-o',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_unfollow'],
        ],
        'menu'     => 'user.user.itemActionMenu',
        'name'     => 'unfollow',
        'label'    => 'user::phrase.unfollow_user',
        'ordering' => 5,
        'value'    => 'user/unfollow',
        'icon'     => 'ico-user2-minus-o',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_follow'],
        ],
        'menu'     => 'user.user.itemActionMenu',
        'name'     => 'follow',
        'label'    => 'user::phrase.follow_user',
        'ordering' => 5,
        'value'    => 'user/follow',
        'icon'     => 'ico-user2-plus-o',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_unfollow'],
        ],
        'menu'     => 'user.user.profilePopoverMenu',
        'name'     => 'unfollow',
        'label'    => 'user::phrase.unfollow_user',
        'ordering' => 3,
        'value'    => 'user/unfollow',
        'icon'     => 'ico-user2-minus-o',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_follow'],
        ],
        'menu'     => 'user.user.profilePopoverMenu',
        'name'     => 'follow',
        'label'    => 'user::phrase.follow_user',
        'ordering' => 3,
        'value'    => 'user/follow',
        'icon'     => 'ico-user2-plus-o',
    ],
];
