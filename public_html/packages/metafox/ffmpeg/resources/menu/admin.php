<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'acl.core.admincp.has_system_access'],
        ],
        'menu'     => 'ffmpeg.admin',
        'name'     => 'settings',
        'label'    => 'core::phrase.settings',
        'to'       => '/ffmpeg/setting',
    ],
];
