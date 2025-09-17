<?php

/* this is auto generated file */
return [
    [
        'type' => 'form-settings',
        'name' => 'google-translate',
        'title' => 'core::phrase.settings',
        'description' => 'google-translate::phrase.edit_google-translate_setting_desc',
        'driver' => 'MetaFox\\GoogleTranslate\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'url' => '/google-translate/setting',
        'resolution'=>'admin',
        'version'=>'v1',
    ],
    [
        'type' => 'package-setting',
        'name' => 'google-translate',
        'driver' => 'MetaFox\\GoogleTranslate\\Http\\Resources\\v1\\PackageSetting',
        'version'=>'v1',
    ],
    [
        'driver'     => 'MetaFox\\GoogleTranslate\\Http\\Resources\\v1\\TranslationGateway\\Admin\\GatewayForm',
        'type'       => 'form',
        'name'       => 'googletranslate.gateway.form',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
];
