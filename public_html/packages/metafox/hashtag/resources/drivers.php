<?php

/* this is auto generated file */
return [
    [
        'driver' => 'MetaFox\\Hashtag\\Models\\Tag',
        'type'   => 'entity',
        'name'   => 'tag',
    ],
    [
        'driver'     => 'MetaFox\\Hashtag\\Http\\Resources\\v1\\Hashtag\\Admin\\TagSettingHashtagForm',
        'type'       => 'form',
        'name'       => 'hashtag.tag_setting',
        'version'    => 'v1',
        'resolution' => 'admin',
    ],
    [
        'driver'  => 'MetaFox\\Hashtag\\Http\\Resources\\v1\\Hashtag\\HashtagItemCollection',
        'type'    => 'json-collection',
        'name'    => 'hashtag.item',
        'version' => 'v1',
    ],
    [
        'driver'  => 'MetaFox\\Hashtag\\Http\\Resources\\v1\\Hashtag\\HashtagItem',
        'type'    => 'json-resource',
        'name'    => 'hashtag.item',
        'version' => 'v1',
    ],
    [
        'driver'  => 'MetaFox\\Hashtag\\Http\\Resources\\v1\\Hashtag\\HashtagSuggestion',
        'type'    => 'json-resource',
        'name'    => 'hashtag.suggestion',
        'version' => 'v1',
    ],
    [
        'driver'  => 'MetaFox\\Hashtag\\Http\\Resources\\v1\\PackageSetting',
        'type'    => 'package-setting',
        'name'    => 'hashtag',
        'version' => 'v1',
    ],
];
