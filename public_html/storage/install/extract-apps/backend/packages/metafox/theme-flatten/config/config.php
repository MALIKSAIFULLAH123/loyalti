<?php

/**
 * stub: packages/config/config.stub.
 */

return [
    'shareAssets' => [
        'images/flatten0.png'         => 'flatten0',
        'images/bg_grey.svg' => 'site_background_default_dark',
        'images/bg_light_grey.svg' => 'site_background_default_light',
    ],
    'themes'      => [
        [
            'theme_id'      => 'flatten',
            'title'         => 'Flatten',
            'resolution'    => 'web',
            'total_variant' => 1,
        ],
    ],
    'styles' => [
        [
            'theme_id'   => 'flatten',
            'variant_id' => 'flatten0',
            'title'      => 'Patten',
        ],
    ],
];
