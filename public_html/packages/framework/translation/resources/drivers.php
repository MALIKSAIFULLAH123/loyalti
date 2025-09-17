<?php

/* this is auto generated file */
return [
    [
        'driver'     => 'MetaFox\\Translation\\Http\\Resources\\v1\\TranslationGateway\\Admin\\DataGrid',
        'type'       => 'data-grid',
        'name'       => 'translation.gateway',
        'version'    => 'v1',
        'resolution' => 'admin',
        'is_active'  => true,
        'is_preload' => false,
        'type_label' => 'Data Grid',
    ],
    [
        'type'       => 'form-settings',
        'name'       => 'translation',
        'title'      => 'core::phrase.settings',
        'driver'     => 'MetaFox\\Translation\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'url'        => '/translation/setting',
        'resolution' => 'admin',
        'version'    => 'v1',
        'is_active'  => true,
        'is_preload' => false,
    ],
    [
        'type'    => 'package-setting',
        'name'    => 'translation',
        'driver'  => 'MetaFox\\Translation\\Http\\Resources\\v1\\PackageSetting',
        'version' => 'v1',
    ],
];
