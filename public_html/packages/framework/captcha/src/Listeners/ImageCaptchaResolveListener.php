<?php

namespace MetaFox\Captcha\Listeners;

use MetaFox\Captcha\Contracts\CaptchaContract;
use MetaFox\Captcha\Support\CaptchaSupport;
use MetaFox\Captcha\Support\ImageCaptcha;

class ImageCaptchaResolveListener
{
    public function handle(string $type): ?CaptchaContract
    {
        if ($type != CaptchaSupport::IMAGE_CAPTCHA_TYPE) {
            return null;
        }

        return resolve(ImageCaptcha::class);
    }
}
