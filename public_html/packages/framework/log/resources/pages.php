<?php

/* this is auto generated file */
return [
    [
        'name'         => 'admin.log.browse_channel',
        'phrase_title' => 'log::phrase.channels',
        'url'          => 'log/channel/browse',
    ],
    [
        'name'         => 'admin.log.browse_file',
        'phrase_title' => 'log::phrase.files',
        'url'          => 'log/file/browse',
    ],
    [
        'name'                 => 'admin.log.browse_file_log',
        'phrase_title'         => 'log::phrase.files',
        'url'                  => 'log/file/{file}/msg/browse',
        'custom_sharing_route' => 1,
    ],
    [
        'name'         => 'admin.log.browse_msg_db',
        'phrase_title' => 'log::phrase.databases',
        'url'          => 'log/db/browse/msg',
    ],
    [
        'name'         => 'admin.log.create_channel',
        'phrase_title' => 'log::phrase.add_new_channel',
        'url'          => 'log/channel/create',
    ],
    [
        'name'                 => 'admin.log.edit_channel',
        'phrase_title'         => 'core::phrase.edit',
        'url'                  => 'log/channel/edit/{driver}/{name}',
        'custom_sharing_route' => 1,
    ],
    [
        'name'         => 'admin.log.log_setting',
        'phrase_title' => 'core::phrase.settings',
        'url'          => 'log/setting',
    ],
];
