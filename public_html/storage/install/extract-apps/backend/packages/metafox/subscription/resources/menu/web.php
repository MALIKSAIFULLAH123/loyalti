<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'or',
            ['truthy', 'setting.subscription.enable_subscription_packages'],
            ['truthy', 'session.user.extra.can_view_subscriptions'],
        ],
        'menu'     => 'core.primaryMenu',
        'name'     => 'subscription',
        'label'    => 'subscription::phrase.subscriptions',
        'ordering' => 5,
        'icon'     => 'ico-address-book',
        'to'       => '/subscription',
    ],
    [
        'tab'      => 'landing',
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn'],
            ['truthy', 'session.user.extra.can_view_subscriptions'],
        ],
        'menu'     => 'subscription.sidebarMenu',
        'name'     => 'my',
        'label'    => 'subscription::phrase.subscription_phrase_sidebar_menu_my_subscriptions',
        'ordering' => 1,
        'icon'     => 'ico-address-book-o',
        'to'       => '/subscription',
    ],
    [
        'tab'      => 'package',
        'showWhen' => [
            'and',
            ['truthy', 'setting.subscription.enable_subscription_packages'],
        ],
        'menu'     => 'subscription.sidebarMenu',
        'name'     => 'landing',
        'label'    => 'subscription::phrase.subscription_phrase_sidebar_menu_packages',
        'ordering' => 2,
        'icon'     => 'ico-box-o',
        'to'       => '/subscription/package',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_edit'],
        ],
        'menu'     => 'subscription.subscription_cancel_reason.itemActionMenu',
        'name'     => 'edit',
        'label'    => 'core::phrase.edit',
        'ordering' => 1,
        'value'    => 'editItem',
        'icon'     => 'ico-pencilline-o',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_delete'],
        ],
        'menu'     => 'subscription.subscription_cancel_reason.itemActionMenu',
        'name'     => 'delete',
        'label'    => 'core::phrase.delete',
        'ordering' => 2,
        'value'    => 'subscription/getDeleteForm',
        'icon'     => 'ico-trash',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_edit'],
        ],
        'menu'     => 'subscription.subscription_comparison.itemActionMenu',
        'name'     => 'edit',
        'label'    => 'core::phrase.edit',
        'ordering' => 1,
        'value'    => 'editItem',
        'icon'     => 'ico-pencilline-o',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_delete'],
        ],
        'menu'     => 'subscription.subscription_comparison.itemActionMenu',
        'name'     => 'delete',
        'label'    => 'core::phrase.delete',
        'ordering' => 2,
        'value'    => 'deleteItem',
        'icon'     => 'ico-trash',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_edit'],
        ],
        'menu'     => 'subscription.subscription_package.itemActionMenu',
        'name'     => 'edit',
        'label'    => 'core::phrase.edit',
        'ordering' => 1,
        'value'    => 'editItem',
        'icon'     => 'ico-pencilline-o',
    ],
    [
        'showWhen' => [
            'and',
            ['falsy', 'item.is_popular'],
        ],
        'menu'     => 'subscription.subscription_package.itemActionMenu',
        'name'     => 'popular',
        'label'    => 'subscription::admin.mark_as_most_popular',
        'ordering' => 2,
        'value'    => 'subscription/popularItem',
        'icon'     => 'ico-star-o',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_view_subscription'],
        ],
        'menu'     => 'subscription.subscription_package.itemActionMenu',
        'name'     => 'view_subscription',
        'label'    => 'subscription::admin.view_subscription',
        'ordering' => 3,
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_delete'],
        ],
        'menu'     => 'subscription.subscription_package.itemActionMenu',
        'name'     => 'delete',
        'label'    => 'core::phrase.delete',
        'ordering' => 4,
        'value'    => 'deleteItem',
        'icon'     => 'ico-trash',
    ],
];
