<?php

/* this is auto generated file */
return [
    [
        'menu'     => 'core.accountMenu',
        'showWhen' => [
            'and',
            ['gte', 'setting.layout.variants.length', 2],
        ],
        'name'      => 'chooseThemes',
        'label'     => 'layout::phrase.theme_variant',
        'ordering'  => 8,
        'value'     => 'chooseTheme',
        'icon'      => 'ico-desktop-text',
        'is_active' => 1,
    ],
];
