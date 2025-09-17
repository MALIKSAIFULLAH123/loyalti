<?php

/* this is auto generated file */
return [
    [
        'showWhen' => [
            'and',
            ['truthy', 'setting.getting-started.enable_getting_started'],
            ['truthy', 'setting.getting-started.has_todo_list'],
        ],
        'menu'     => 'core.accountMenu',
        'name'     => 'todo_list',
        'label'    => 'getting-started::phrase.getting_started',
        'ordering' => 7,
        'icon'     => 'ico-list-o',
        'value'    => 'getting-started/gettingStarted',
    ],
];
