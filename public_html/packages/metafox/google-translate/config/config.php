<?php

/**
 * stub: packages/config/config.stub.
 */

return [
    'name' => 'Google Translate',
    'translation_gateways' => [
        'google_translate' => [
            'service'     => 'googletranslate',
            'module_id'   => 'googletranslate',
            'is_active'   => 1,
            'title'       => 'Google Translate',
            'description' => 'Google Translate Gateway',
            'config'      => [
                'api_key' => env('MFOX_GOOGLE_MAP_API_KEY', ''),
            ],
            'service_class' => \MetaFox\GoogleTranslate\Support\GoogleTranslate::class,
        ],
    ],
];
