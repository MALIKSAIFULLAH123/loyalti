<?php

/* this is auto generated file */
return [
    [
        'driver'     => 'MetaFox\\Sms\\Http\\Resources\\v1\\Service\\Admin\\DataGrid',
        'type'       => 'data-grid',
        'name'       => 'sms.service',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'      => 'MetaFox\\Sms\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'type'        => 'form-settings',
        'name'        => 'sms',
        'version'     => 'v1',
        'resolution'  => 'admin',
        'title'       => 'core::phrase.settings',
        'url'         => '/sms/setting',
        'description' => 'SMS Settings',
    ],
    [
        'driver'  => 'MetaFox\\Sms\\Http\\Resources\\v1\\PackageSetting',
        'type'    => 'package-setting',
        'name'    => 'sms',
        'version' => 'v1',
    ],
    [
        'driver'  => 'MetaFox\\Sms\\Support\\Services\\LogService',
        'type'    => 'sms-service',
        'name'    => 'log',
        'version' => 'v1',
    ],
    [
        'driver'     => 'MetaFox\\Sms\\Http\\Resources\\v1\\Admin\\ServiceLogSettingForm',
        'type'       => 'sms-service-form',
        'name'       => 'log',
        'version'    => 'v1',
        'resolution' => 'admin',
        'title'      => 'sms::phrase.log_service',
        'url'        => '/sms/service/edit/log',
    ],
];
