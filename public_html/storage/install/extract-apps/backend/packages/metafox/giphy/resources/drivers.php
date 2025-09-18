<?php

/* this is auto generated file */
return [
    [
        'type'        => 'form-settings',
        'name'        => 'giphy',
        'title'       => 'core::phrase.settings',
        'description' => 'giphy::phrase.edit_giphy_setting_desc',
        'driver'      => 'MetaFox\\Giphy\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'url'         => '/giphy/setting',
        'resolution'  => 'admin',
        'version'     => 'v1',
    ],
    [
        'type'    => 'package-setting',
        'name'    => 'giphy',
        'driver'  => 'MetaFox\Giphy\\Http\\Resources\\v1\\PackageSetting',
        'version' => 'v1',
    ],
    [
        'driver'     => 'MetaFox\\Giphy\\Http\\Resources\\v1\\Gif\\WebSetting',
        'type'       => 'resource-web',
        'name'       => 'gif',
        'version'    => 'v1',
        'resolution' => 'web',
        'is_active'  => true,
        'is_preload' => false,
    ],
    [
        'driver'     => 'MetaFox\\Giphy\\Http\\Resources\\v1\\Gif\\MobileSetting',
        'type'       => 'resource-mobile',
        'name'       => 'gif',
        'version'    => 'v1',
        'resolution' => 'mobile',
        'is_active'  => true,
        'is_preload' => false,
    ],
    [
        'driver'     => 'MetaFox\\Giphy\\Http\\Resources\\v1\\Gif\\SearchGifForm',
        'type'       => 'form',
        'name'       => 'giphy.gif.search_form',
        'version'    => 'v1',
        'resolution' => 'web',
        'is_active'  => true,
        'is_preload' => false,
    ],
    [
        'driver'     => 'MetaFox\\Giphy\\Http\\Resources\\v1\\Gif\\SearchGifMobileForm',
        'type'       => 'form',
        'name'       => 'giphy.gif.search_form',
        'version'    => 'v1',
        'resolution' => 'mobile',
        'is_active'  => true,
        'is_preload' => false,
    ],
];
