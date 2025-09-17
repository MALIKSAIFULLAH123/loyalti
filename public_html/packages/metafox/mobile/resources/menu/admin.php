<?php

/* this is auto generated file */
return [
    [
        'showWhen'    => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'        => 'core.adminSidebarMenu',
        'name'        => 'mobile',
        'parent_name' => 'app-settings',
        'label'       => 'mobile::phrase.app_name',
        'testid'      => 'mobile',
        'to'          => '/mobile/setting',
    ],
    [
        'menu'     => 'mobile.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'to'       => '/mobile/setting',
        'ordering' => 1,
    ],
    [
        'menu'     => 'mobile.admin',
        'name'     => 'smart_banner',
        'label'    => 'mobile::phrase.smart_banner_configs',
        'ordering' => 2,
        'to'       => '/mobile/setting/smart-banner',
    ],
    [
        'menu'     => 'mobile.admin',
        'name'     => 'admob_config',
        'label'    => 'mobile::phrase.manage_ad_config',
        'to'       => '/mobile/admob/browse',
        'ordering' => 3,
    ],
];
