<?php

return [
    'name'    => 'Forum',
    'models'  => [
        'Forum'          => [
            'resource'    => [
                'configs' => [
                    'v1' => ['web', 'mobile'],
                ],
            ],
            'entity_type' => 'forum',
            'admincp'     => [
                'resource' => [
                ],
                'forms'    => [
                    'v1' => ['forum-setting'],
                ],
            ],
            'forms'       => [
                'v1' => [
                    'create',
                    'edit',
                ],
            ],
        ],
        'ForumThread' => [
            'resource'    => [
                'configs' => [
                    'v1' => ['web', 'mobile'],
                ],
            ],
            'entity_type' => 'forum_thread',
            'admincp'     => [],
            'forms'       => [
                'v1' => [
                    'create',
                    'edit',
                    'search',
                ],
            ],
        ],
        'ForumPost' => [
            'resource'    => [
                'configs' => [
                    'v1' => ['web', 'mobile'],
                ],
            ],
            'entity_type' => 'forum_post',
            'admincp'     => [],
            'forms'       => [
                'v1' => [
                    'create',
                    'edit',
                    'search',
                ],
            ],
        ],
    ],
];
