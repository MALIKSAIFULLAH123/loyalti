<?php

/* this is auto generated file */
return [
    [
        'driver'     => 'MetaFox\\Schedule\\Http\\Resources\\v1\\Job\\Admin\\DataGrid',
        'type'       => 'data-grid',
        'name'       => 'schedule.job',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'      => 'MetaFox\\Schedule\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'type'        => 'form-settings',
        'name'        => 'schedule',
        'version'     => 'v1',
        'resolution'  => 'admin',
        'is_preload'  => 1,
        'title'       => 'schedule::phrase.schedule_settings',
        'url'         => '/schedule/setting',
        'description' => 'Schedule Settings',
    ],
    [
        'driver'  => 'MetaFox\\Schedule\\Http\\Resources\\v1\\PackageSetting',
        'type'    => 'package-setting',
        'name'    => 'schedule',
        'version' => 'v1',
    ],
];
