<?php

return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'setting.getting-started.enable_getting_started'],
            ['truthy', 'setting.getting-started.has_todo_list'],
        ],
        'menu'      => 'core.bodyMenu',
        'name'      => 'gettingstarted',
        'label'     => 'getting-started::phrase.getting_started',
        'ordering'  => 1,
        'value'     => '',
        'to'        => '/getting_started/todo_list',
        'as'        => 'item',
        'icon'      => 'list-o',
        'iconColor' => '#6b95f6',
    ],
];
