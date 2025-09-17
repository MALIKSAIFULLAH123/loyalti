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
        'ordering' => 4,
        'value'    => 'group/follow',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_unfollow'],
        ],
        'menu'     => 'group.group.profileActionMenu',
        'name'     => 'unfollow',
        'label'    => 'group::phrase.unfollow_group',
        'ordering' => 4,
        'value'    => 'group/unfollow',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_follow'],
        ],
        'menu'     => 'page.page.profileActionMenu',
        'name'     => 'follow',
        'label'    => 'page::phrase.follow_page',
        'ordering' => 4,
        'value'    => 'page/follow',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_unfollow'],
        ],
        'menu'     => 'page.page.profileActionMenu',
        'name'     => 'unfollow',
        'label'    => 'page::phrase.unfollow_page',
        'ordering' => 4,
        'value'    => 'page/unfollow',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_follow'],
        ],
        'menu'     => 'user.user.profileActionMenu',
        'name'     => 'follow',
        'label'    => 'user::phrase.follow_user',
        'ordering' => 6,
        'value'    => 'user/follow',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_unfollow'],
        ],
        'menu'     => 'user.user.profileActionMenu',
        'name'     => 'unfollow',
        'label'    => 'user::phrase.unfollow_user',
        'ordering' => 6,
        'value'    => 'user/unfollow',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_follow'],
            ['eq', 'item.friendship', 0],
        ],
        'menu'     => 'user.user.detailActionMenu',
        'name'     => 'follow',
        'label'    => 'user::phrase.follow_user',
        'ordering' => 3,
        'value'    => 'user/follow',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_unfollow'],
            ['eq', 'item.friendship', 0],
        ],
        'menu'     => 'user.user.detailActionMenu',
        'name'     => 'unfollow',
        'label'    => 'user::phrase.unfollow_user',
        'ordering' => 3,
        'value'    => 'user/unfollow',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_follow'],
        ],
        'menu'     => 'user.user.itemActionMenu',
        'name'     => 'follow',
        'label'    => 'user::phrase.follow_user',
        'ordering' => 6,
        'value'    => 'user/follow',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_unfollow'],
        ],
        'menu'     => 'user.user.itemActionMenu',
        'name'     => 'unfollow',
        'label'    => 'user::phrase.unfollow_user',
        'ordering' => 6,
        'value'    => 'user/unfollow',
    ],
];
