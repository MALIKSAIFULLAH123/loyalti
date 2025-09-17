<?php

/* this is auto generated file */
return [
    [
        'menu'     => 'mux.admin',
        'name'     => 'video_settings',
        'label'    => 'mux::phrase.mux_video_config',
        'to'       => '/mux/setting/video',
        'showWhen' => [
            'and',
            ['truthy', 'settings.video'],
        ],
    ],
    [
        'menu'     => 'mux.admin',
        'name'     => 'live_streaming_settings',
        'label'    => 'mux::phrase.live_streaming_settings',
        'to'       => '/mux/setting/livestreaming',
        'showWhen' => [
            'and',
            ['truthy', 'settings.livestreaming'],
        ],
    ],
    [
        'menu'     => 'mux.admin',
        'name'     => 'story_settings',
        'label'    => 'mux::phrase.story_settings',
        'to'       => '/mux/setting/story',
        'showWhen' => [
            'and',
            ['truthy', 'settings.story'],
        ],
    ],
];
