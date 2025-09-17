<?php

/* this is auto generated file */
return [
    [
        'type'        => 'form-settings',
        'name'        => 'follow',
        'title'       => 'core::phrase.settings',
        'description' => 'follow::phrase.edit_follow_setting_desc',
        'driver'      => 'MetaFox\\Follow\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'url'         => '/follow/setting',
        'resolution'  => 'admin',
        'version'     => 'v1',
        'is_active'   => 0,
    ],
    [
        'type'    => 'package-setting',
        'name'    => 'follow',
        'driver'  => 'MetaFox\\Follow\\Http\\Resources\\v1\\PackageSetting',
        'version' => 'v1',
    ],
    [
        'driver' => 'MetaFox\\Follow\\Models\\Follow',
        'type'   => 'entity',
        'name'   => 'follow',
    ],
    [
        'driver'  => 'MetaFox\\Follow\\Http\\Resources\\v1\\MobileSetting',
        'type'    => 'package-mobile',
        'name'    => 'follow',
        'version' => 'v1',
    ],
    [
        'driver'  => 'MetaFox\\Follow\\Http\\Resources\\v1\\WebSetting',
        'type'    => 'package-web',
        'name'    => 'follow',
        'version' => 'v1',
    ],
    [
        'driver'     => 'MetaFox\\Follow\\Http\\Resources\\v1\\Follow\\MobileSetting',
        'type'       => 'resource-mobile',
        'name'       => 'follow',
        'version'    => 'v1',
        'resolution' => 'mobile',
    ],
    [
        'driver'     => 'MetaFox\\Follow\\Http\\Resources\\v1\\Follow\\WebSetting',
        'type'       => 'resource-web',
        'name'       => 'follow',
        'version'    => 'v1',
        'resolution' => 'web',
    ],
    [
        'driver'     => 'MetaFox\\Follow\\Http\\Resources\\v1\\Follow\\SearchUserMobileForm',
        'type'       => 'form',
        'name'       => 'follow.search',
        'version'    => 'v1',
        'resolution' => 'mobile',
        'is_active'  => true,
        'is_preload' => true,
        'type_label' => 'Form',
    ],
];
