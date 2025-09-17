<?php

namespace MetaFox\Captcha\Rules;

use Illuminate\Contracts\Validation\Rule;
use MetaFox\Captcha\Support\RecaptchaV3;

class ReCaptchaV3Rule implements Rule
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

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        $captcha = resolve(RecaptchaV3::class);

        if (null === $captcha) {
            return false;
        }

        return $captcha->verify($value, $this->actionName);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __p('validation.recaptcha_v3');
    }
}
