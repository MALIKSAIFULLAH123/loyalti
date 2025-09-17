<?php

/* this is auto generated file */
return [
    [
        'driver'     => 'MetaFox\\StaticPage\\Http\\Resources\\v1\\StaticPage\\Admin\\DataGrid',
        'type'       => 'data-grid',
        'name'       => 'static_page.page',
        'version'    => 'v1',
        'resolution' => 'admin',
        'is_active'  => true,
        'is_preload' => false,
    ],
    [
        'driver'     => 'MetaFox\\StaticPage\\Models\\StaticPage',
        'type'       => 'entity',
        'name'       => 'static_page',
        'is_active'  => true,
        'is_preload' => false,
        'title'      => 'Static Pages',
    ],
    [
        'driver'     => 'MetaFox\\StaticPage\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'type'       => 'form-settings',
        'name'       => 'static_page',
        'version'    => 'v1',
        'resolution' => 'admin',
        'is_active'  => true,
        'is_preload' => false,
        'url'        => '/static-page/setting',
    ],
    [
        'driver'     => 'MetaFox\\StaticPage\\Http\\Resources\\v1\\PackageSetting',
        'type'       => 'package-setting',
        'name'       => 'staticpage',
        'version'    => 'v1',
        'is_active'  => true,
        'is_preload' => false,
    ],
    [
        'driver'     => 'MetaFox\\StaticPage\\Http\\Resources\\v1\\StaticPage\\WebSetting',
        'type'       => 'resource-web',
        'name'       => 'static_page',
        'version'    => 'v1',
        'resolution' => 'web',
        'is_active'  => true,
        'is_preload' => false,
    ],
];
