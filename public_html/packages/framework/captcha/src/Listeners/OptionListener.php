<?php

namespace MetaFox\Captcha\Listeners;

use MetaFox\Captcha\Support\CaptchaSupport;

class OptionListener
{
    public function handle(): array
    {
        return [
            [
                'label' => __p('captcha::admin.recaptcha_v3'),
                'value' => CaptchaSupport::RECAPTCHA_V3_TYPE,
            ],
            [
                'label' => __p('captcha::admin.image_captcha'),
                'value' => CaptchaSupport::IMAGE_CAPTCHA_TYPE,
            ],
        ];
    }
}
