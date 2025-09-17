<?php

/* this is auto generated file */
return [
    [
        'name'       => 'ewallet.sidebarMenu',
        'resolution' => 'web',
        'type'       => 'sidebar',
    ],
    [
        'name'       => 'ewallet.admin',
        'resolution' => 'admin',
        'type'       => 'admin_top',
    ],
    [
        'name'          => 'ewallet.ewallet_withdraw_request.itemActionMenu',
        'resource_name' => 'ewallet_withdraw_request',
        'resolution'    => 'web',
        'type'          => 'context',
    ],

    //Backup for mobile version which < 5.1.6
    [
        'name'       => 'emoney.sidebarMenu',
        'resolution' => 'mobile',
        'type'       => 'sidebar',
    ],
    [
        'name'          => 'emoney.emoney_withdraw_request.itemActionMenu',
        'resource_name' => 'emoney_withdraw_request',
        'resolution'    => 'mobile',
        'type'          => 'context',
    ],
    [
        'name'          => 'emoney.emoney_withdraw_request.detailActionMenu',
        'resource_name' => 'emoney_withdraw_request',
        'resolution'    => 'mobile',
        'type'          => 'context',
    ],

    //New for mobile version which >= 5.1.6
    [
        'name'       => 'ewallet.sidebarMenu',
        'resolution' => 'mobile',
        'type'       => 'sidebar',
    ],
    [
        'name'          => 'ewallet.ewallet_withdraw_request.itemActionMenu',
        'resource_name' => 'ewallet_withdraw_request',
        'resolution'    => 'mobile',
        'type'          => 'context',
    ],
    [
        'name'          => 'ewallet.ewallet_withdraw_request.detailActionMenu',
        'resource_name' => 'ewallet_withdraw_request',
        'resolution'    => 'mobile',
        'type'          => 'context',
    ],
];
