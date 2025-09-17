<?php

/* this is auto generated file */
return [
    [
        'driver'     => 'MetaFox\\Importer\\Http\\Resources\\v1\\Bundle\\Admin\\DataGrid',
        'type'       => 'data-grid',
        'name'       => 'importer.bundle',
        'version'    => 'v1',
        'resolution' => 'admin',
        'title'      => 'Data Grid Settings',
    ],
    [
        'driver'     => 'MetaFox\\Importer\\Http\\Resources\\v1\\Entry\\Admin\\DataGrid',
        'type'       => 'data-grid',
        'name'       => 'importer.bundle.entry',
        'version'    => 'v1',
        'resolution' => 'admin',
        'title'      => 'Data Grid Settings',
    ],
    [
        'driver'     => 'MetaFox\\Importer\\Http\\Resources\\v1\\Log\\Admin\\DataGrid',
        'type'       => 'data-grid',
        'name'       => 'importer.log',
        'version'    => 'v1',
        'resolution' => 'admin',
        'title'      => 'Data Grid Settings',
        'is_deleted' => true,
    ],
    [
        'driver'     => 'MetaFox\\Importer\\Http\\Resources\\v1\\Log\\Admin\\DataGrid',
        'type'       => 'data-grid',
        'name'       => 'importer.history',
        'version'    => 'v1',
        'resolution' => 'admin',
        'title'      => 'Data Grid Settings',
    ],
    [
        'driver'      => 'MetaFox\\Importer\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'type'        => 'form-settings',
        'name'        => 'importer',
        'version'     => 'v1',
        'resolution'  => 'admin',
        'title'       => 'core::phrase.settings',
        'url'         => '/importer/setting',
        'description' => 'importer::phrase.edit_migrate_setting_desc',
    ],
    [
        'driver'  => 'MetaFox\\Importer\\Http\\Resources\\v1\\PackageSetting',
        'type'    => 'package-setting',
        'name'    => 'importer',
        'version' => 'v1',
    ],
    [
        'driver' => 'MetaFox\\Importer\\Jobs\\ImportMonitor',
        'type'   => 'job',
        'name'   => 'MetaFox\\Importer\\Jobs\\ImportMonitor',
    ],
];
