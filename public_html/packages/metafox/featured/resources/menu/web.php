<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn'],
        ],
        'menu'     => 'core.primaryFooterMenu',
        'name'     => 'feature',
        'label'    => 'featured::phrase.app_name',
        'ordering' => 1,
        'to'       => '/featured',
    ],
    [
        'tab'      => 'items',
        'menu'     => 'featured.sidebarMenu',
        'name'     => 'items',
        'label'    => 'core::web.items',
        'ordering' => 1,
        'icon'     => 'ico-diamond-o',
        'to'       => '/featured',
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn'],
        ],
    ],
    [
        'tab'      => 'invoices',
        'menu'     => 'featured.sidebarMenu',
        'name'     => 'invoices',
        'label'    => 'core::web.invoices',
        'ordering' => 2,
        'icon'     => 'ico-merge-file-o',
        'to'       => '/featured/invoice',
        'showWhen' => [
            'and',
            ['truthy', 'session.loggedIn'],
        ],
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_payment'],
        ],
        'menu'     => 'featured.featured_item.itemActionMenu',
        'name'     => 'payment',
        'label'    => 'featured::phrase.pay_price',
        'ordering' => 1,
        'value'    => 'featured/paymentItem',
        'icon'     => 'credit-card',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_cancel'],
        ],
        'menu'     => 'featured.featured_item.itemActionMenu',
        'name'     => 'cancel',
        'label'    => 'core::phrase.cancel',
        'ordering' => 2,
        'value'    => 'featured/cancelItem',
        'icon'     => 'close-circle',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_delete'],
        ],
        'menu'     => 'featured.featured_item.itemActionMenu',
        'name'     => 'delete',
        'label'    => 'core::phrase.delete',
        'ordering' => 3,
        'value'    => 'deleteItem',
        'icon'     => 'trash',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_payment'],
        ],
        'menu'     => 'featured.featured_invoice.itemActionMenu',
        'name'     => 'payment',
        'label'    => 'featured::phrase.pay_now',
        'ordering' => 1,
        'value'    => 'featured/paymentItem',
        'icon'     => 'credit-card',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'item.extra.can_cancel'],
        ],
        'menu'     => 'featured.featured_invoice.itemActionMenu',
        'name'     => 'cancel',
        'label'    => 'core::phrase.cancel',
        'ordering' => 2,
        'value'    => 'featured/cancelItem',
        'icon'     => 'close-circle',
    ],
];
