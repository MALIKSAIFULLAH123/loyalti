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
        'to'       => '/subscription',
    ],
    [
        'tab'      => 'landing',
        'showWhen' => [
            'and',
            ['truthy', 'setting.subscription.enable_subscription_packages'],
        ],
        'params' => [
            'module_name'   => 'subscription',
            'resource_name' => 'subscription_package',
        ],
        'menu'     => 'subscription.sidebarMenu',
        'name'     => 'landing',
        'label'    => 'subscription::phrase.subscription_phrase_sidebar_menu_packages',
        'ordering' => 3,
        'value'    => 'viewAll',
        'to'       => '/subscription',
    ],
    [
        'tab'      => 'my',
        'showWhen' => [
            'and',
            ['truthy', 'session.user.extra.can_view_subscriptions'],
        ],
        'params' => [
            'module_name'   => 'subscription',
            'resource_name' => 'subscription_invoice',
        ],
        'menu'     => 'subscription.sidebarMenu',
        'name'     => 'my',
        'label'    => 'subscription::phrase.subscription_phrase_sidebar_menu_my_subscriptions',
        'ordering' => 2,
        'value'    => 'viewAll',
        'to'       => '/subscription/my',
    ],
    [
        'showWhen' => [
            'or',
            ['truthy', 'setting.subscription.enable_subscription_packages'],
            ['truthy', 'session.user.extra.can_view_subscriptions'],
        ],
        'menu'      => 'core.bodyMenu',
        'name'      => 'subscription',
        'label'     => 'subscription::phrase.subscriptions',
        'ordering'  => 15,
        'value'     => '',
        'to'        => '/subscription',
        'as'        => 'item',
        'icon'      => 'address-book',
        'iconColor' => '#ff4986',
    ],
];
