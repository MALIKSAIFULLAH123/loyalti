<?php

return [
    [
        'menu'     => 'core.accountMenu',
        'name'     => 'create_tour',
        'label'    => 'tourguide::phrase.create_guide',
        'ordering' => 4,
        'icon'     => 'ico-tourguide',
        'value'    => 'tourguide/createTour',
        'showWhen' => [
            'and',
            ['truthy', 'setting.tourguide.can_create_tourguide'],
        ],
    ],
];
