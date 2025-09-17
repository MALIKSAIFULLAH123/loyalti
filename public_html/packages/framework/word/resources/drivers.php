<?php

/* this is auto generated file */
return [
    [
        'driver'     => 'MetaFox\\Word\\Http\\Resources\\v1\\Block\\Admin\\DataGrid',
        'type'       => 'data-grid',
        'name'       => 'word.block',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'     => 'MetaFox\\Word\\Http\\Resources\\v1\\Block\\Admin\\StoreBlockForm',
        'type'       => 'form',
        'name'       => 'word.block.store',
        'version'    => 'v1',
        'resolution' => 'admin',
        'title'      => 'word::block.edit_word',
    ],
    [
        'driver'      => 'MetaFox\\Word\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'type'        => 'form-settings',
        'name'        => 'word',
        'version'     => 'v1',
        'resolution'  => 'admin',
        'title'       => 'core::phrase.settings',
        'url'         => 'admincp/word/setting',
        'description' => 'Bad-word Settings',
    ],
    [
        'driver'  => 'MetaFox\\Word\\Http\\Resources\\v1\\PackageSetting',
        'type'    => 'package-setting',
        'name'    => 'word',
        'version' => 'v1',
    ],
];
