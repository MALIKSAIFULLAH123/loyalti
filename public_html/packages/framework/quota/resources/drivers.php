<?php

/* this is auto generated file */
return [
    [
        'driver'     => 'MetaFox\\QuotaControl\\Http\\Resources\\v1\\Admin\\EditPermissionForm',
        'type'       => 'form',
        'name'       => 'quota.edit_permission',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'     => 'MetaFox\\QuotaControl\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'type'       => 'form-settings',
        'name'       => 'quota',
        'version'    => 'v1',
        'resolution' => 'admin',
        'title'      => 'core::phrase.settings',
        'url'        => '/quota/setting',
    ],
    [
        'driver'  => 'MetaFox\\QuotaControl\\Http\\Resources\\v1\\PackageSetting',
        'type'    => 'package-setting',
        'name'    => 'quota',
        'version' => 'v1',
    ],
];
