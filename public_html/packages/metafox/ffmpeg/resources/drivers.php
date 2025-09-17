<?php

/* this is auto generated file */
return [
    [
        'type'        => 'form-settings',
        'name'        => 'ffmpeg',
        'title'       => 'core::phrase.settings',
        'description' => 'ffmpeg::phrase.edit_ffmpeg_setting_desc',
        'driver'      => 'MetaFox\\FFMPEG\\Http\\Resources\\v1\\Admin\\SiteSettingForm',
        'url'         => '/ffmpeg/setting',
        'resolution'  => 'admin',
        'version'     => 'v1',
    ],
    [
        'type'    => 'package-setting',
        'name'    => 'ffmpeg',
        'driver'  => 'MetaFox\\FFMPEG\\Http\\Resources\\v1\\PackageSetting',
        'version' => 'v1',
    ],
    [
        'type'       => 'video-service',
        'name'       => 'ffmpeg',
        'driver'     => 'MetaFox\\FFMPEG\\Support\\Providers\\FFMPEG',
        'version'    => 'v1',
        'resolution' => 'admin',
        'title'      => 'ffmpeg::phrase.ffmpeg',
        'url'        => '/ffmpeg/setting',
    ],
    [
        'type'       => 'story-service',
        'name'       => 'ffmpeg',
        'driver'     => 'MetaFox\\FFMPEG\\Support\\Providers\\FFMPEG',
        'version'    => 'v1',
        'resolution' => 'admin',
        'title'      => 'ffmpeg::phrase.ffmpeg',
        'url'        => '/ffmpeg/setting',
    ],
];
