<?php

/* this is auto generated file */
return [
    [
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'in-app-purchase',
        'parent_name' => 'app-settings',
        'label'       => 'In-App Purchase',
        'testid'      => '/in-app-purchase/setting',
        'to'          => '/in-app-purchase/setting',
    ],
    [
        'menu'     => 'in-app-purchase.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'ordering' => 1,
        'to'       => '/in-app-purchase/setting',
    ],
    [
        'menu'     => 'in-app-purchase.admin',
        'name'     => 'google_service_account',
        'label'    => 'in-app-purchase::phrase.google_service_account',
        'ordering' => 2,
        'to'       => '/in-app-purchase/setting/google-service-account',
    ],
    [
        'menu'     => 'in-app-purchase.admin',
        'name'     => 'iap_product',
        'label'    => 'in-app-purchase::phrase.manage_products',
        'to'       => '/in-app-purchase/product/browse',
        'ordering' => 3,
    ],
];
