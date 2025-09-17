<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'featured',
        'parent_name' => 'app-settings',
        'label'       => 'featured::phrase.app_name',
        'testid'      => '/featured/package/browse',
        'to'          => '/featured/package/browse',
    ],
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'featured.admin',
        'name'     => 'featured_settings',
        'label'    => 'featured::admin.featured_settings',
        'to'       => '/featured/setting/item',
        'ordering' => 1,
    ],
    [
        'menu'     => 'featured.admin',
        'name'     => 'packages',
        'label'    => 'featured::admin.packages',
        'to'       => '/featured/package/browse',
        'ordering' => 2,
    ],
    [
        'menu'     => 'featured.admin',
        'name'     => 'invoices',
        'label'    => 'core::web.invoices',
        'to'       => '/featured/invoice/browse',
        'ordering' => 4,
    ],
    [
        'menu'     => 'featured.admin',
        'name'     => 'transactions',
        'label'    => 'featured::admin.transactions',
        'to'       => '/featured/transaction/browse',
        'ordering' => 5,
    ],
];
