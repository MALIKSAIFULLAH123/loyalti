<?php

use MetaFox\Captcha\Support\CaptchaSupport;

return [
    'types.' . CaptchaSupport::RECAPTCHA_V3_TYPE => [
        'config_name' => 'captcha.types.' . CaptchaSupport::RECAPTCHA_V3_TYPE,
        'value'       => [
            'type'        => CaptchaSupport::RECAPTCHA_V3_TYPE,
            'description' => 'captcha::admin.recaptcha_v3_description',
        ],
        'env_var'   => '',
        'type'      => 'array',
        'is_public' => 0,
    ],
    'types.' . CaptchaSupport::IMAGE_CAPTCHA_TYPE => [
        'config_name' => 'captcha.types.' . CaptchaSupport::IMAGE_CAPTCHA_TYPE,
        'value'       => [
            'type'        => CaptchaSupport::IMAGE_CAPTCHA_TYPE,
            'description' => 'captcha::admin.image_captcha_description',
        ],
        'env_var'   => '',
        'type'      => 'array',
        'is_public' => 0,
    ],
    CaptchaSupport::RECAPTCHA_V3_TYPE . '.site_key' => [
        'is_public' => 0,
        'env_var'   => 'MFOX_GOOGLE_RECAPTCHA_KEY',
        'value'     => '',
    ],
    CaptchaSupport::RECAPTCHA_V3_TYPE . '.secret' => [
        'is_public' => 0,
        'env_var'   => 'MFOX_GOOGLE_RECAPTCHA_SECRET',
        'value'     => '',
    ],
    CaptchaSupport::RECAPTCHA_V3_TYPE . '.min_score' => [
        'is_public' => 0,
        'value'     => 0.5,
    ],
    'rules' => [
        'type'  => 'array',
        'value' => [],
    ],
];
