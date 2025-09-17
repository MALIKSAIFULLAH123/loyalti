<?php

namespace MetaFox\Captcha\Listeners;

use MetaFox\Captcha\Contracts\CaptchaContract;
use MetaFox\Captcha\Support\CaptchaSupport;
use MetaFox\Captcha\Support\RecaptchaV3;

class RecaptchaV3ResolveListener
{
    public function handle(string $type): ?CaptchaContract
    {
        if ($type != CaptchaSupport::RECAPTCHA_V3_TYPE) {
            return null;
        }

        return resolve(RecaptchaV3::class);
    }
}
