<?php

/* this is auto generated file */
return [
    [
        'driver'     => 'MetaFox\\ChatPlus\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'type'       => 'form-settings',
        'name'       => 'chatplus',
        'version'    => 'v1',
        'resolution' => 'admin',
        'title'      => 'core::phrase.settings',
        'url'        => '/chatplus/setting',
    ],
    [
        'driver'     => 'MetaFox\\ChatPlus\\Http\\Resources\\v1\\User\\Admin\\UserSettingForm',
        'type'       => 'form-settings',
        'name'       => 'chatplus.user',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'  => 'MetaFox\\ChatPlus\\Http\\Resources\\v1\\User\\UserItemCollection',
        'type'    => 'json-collection',
        'name'    => 'chatplus.item',
        'version' => 'v1',
    ],
    [
        'driver'  => 'MetaFox\\ChatPlus\\Http\\Resources\\v1\\User\\UserItem',
        'type'    => 'json-resource',
        'name'    => 'chatplus.item',
        'version' => 'v1',
    ],
    [
        'driver'  => 'MetaFox\\ChatPlus\\Http\\Resources\\v1\\MobileSetting',
        'type'    => 'package-mobile',
        'name'    => 'chatplus',
        'version' => 'v1',
    ],
    [
        'driver'  => 'MetaFox\\ChatPlus\\Http\\Resources\\v1\\PackageSetting',
        'type'    => 'package-setting',
        'name'    => 'chatplus',
        'version' => 'v1',
    ],
    [
        'driver'  => 'MetaFox\\ChatPlus\\Http\\Resources\\v1\\WebSetting',
        'type'    => 'package-web',
        'name'    => 'chatplus',
        'version' => 'v1',
    ],
];
