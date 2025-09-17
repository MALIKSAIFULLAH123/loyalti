<?php

namespace MetaFox\Captcha\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\Captcha\Support\ImageCaptcha;

class ImageCaptchaRule implements Rule
{
    protected ?string $actionName;

    /**
     * @return string|null
     */
    public function getActionName(): ?string
    {
        return $this->actionName;
    }

    /**
     * RecaptchaV3Rule constructor.
     *
     * @param string|null $actionName
     */
    public function __construct(?string $actionName = null)
    {
        $this->actionName  = $actionName;
    }

    public function passes($attribute, $value): bool
    {
        $captcha = resolve(ImageCaptcha::class);

        if (null === $captcha) {
            return false;
        }

        return $captcha->verify($value, $this->actionName);
    }

    public function message(): string
    {
        return __p('validation.image_captcha');
    }
}
