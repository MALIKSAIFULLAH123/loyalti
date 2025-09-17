<?php

namespace MetaFox\Captcha\Support;

use Illuminate\Support\Arr;
use MetaFox\Captcha\Contracts\CaptchaContract;
use MetaFox\Captcha\Support\Contracts\CaptchaSupportContract;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Section;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\PackageManager;

class CaptchaSupport implements CaptchaSupportContract
{
    public const RECAPTCHA_V3_TYPE  = 'recaptcha_v3';

    public const IMAGE_CAPTCHA_TYPE  = 'image_captcha';

    protected ?CaptchaContract $captcha = null;

    public function __construct()
    {
        $this->resolveCaptcha();
    }

    public function verify(string $token, ?string $action = null): bool
    {
        if (null === $this->captcha) {
            return true;
        }

        return $this->captcha->verify($token, $action);
    }

    public function ruleOf(?string $action = null): array
    {
        $optionalRule = ['sometimes', 'nullable'];

        if (null === $this->captcha) {
            return $optionalRule;
        }

        return $this->captcha->ruleOf($action);
    }

    public function errorMessage(?string $action = null): ?string
    {
        if (null === $this->captcha) {
            return null;
        }

        return $this->captcha->errorMessage($action);
    }

    public function resolveCaptcha(?string $type = null): void
    {
        if (MetaFox::isMobile()) {
            return;
        }

        if (null === $type) {
            $type = Settings::get('captcha.default', self::RECAPTCHA_V3_TYPE);
        }

        $this->captcha = app('events')->dispatch('captcha.resolve', [$type], true);

        if (!$this->captcha instanceof CaptchaContract) {
            $this->captcha = null;
        }
    }

    public function getFormField(?string $action = null, string $resolution = 'web', bool $isPreload = false, string $fieldName = 'captcha', bool $isHidden = true): ?AbstractField
    {
        if (null === $this->captcha) {
            return null;
        }

        if (null !== $action) {
            if (!Settings::get('captcha.rules.' . $action)) {
                return null;
            }
        }

        return $this->captcha->generateFormField($action, $resolution, $isPreload, $fieldName, $isHidden);
    }

    public function getDefaultCaptchaType(): string
    {
        return self::RECAPTCHA_V3_TYPE;
    }

    public function getOptions(): array
    {
        $options = app('events')->dispatch('captcha.options');

        $parsed = [];

        foreach ($options as $option) {
            if (is_array($option)) {
                $parsed = array_merge($parsed, $option);
            }
        }

        return $parsed;
    }

    public function addFormField(Section $basic, ?string $action = null, string $resolution = 'web', bool $isPreload = false, string $fieldName = 'captcha'): Section
    {
        $field = $this->getFormField($action, $resolution, $isPreload, $fieldName);

        if (null == $field) {
            return $basic;
        }

        $basic->addField($field);

        return $basic;
    }

    public function refresh(?string $action = null): array
    {
        if (null === $this->captcha) {
            return [];
        }

        if (null !== $action) {
            if (!Settings::get('captcha.rules.' . $action)) {
                return [];
            }
        }

        return $this->captcha->refresh();
    }

    public function ruleMessage(?string $action = null, string $name = 'captcha'): ?array
    {
        if (null === $this->captcha) {
            return null;
        }

        return $this->captcha->ruleMessage($action, $name);
    }

    public function getRules(): array
    {
        $rules = PackageManager::discoverSettings('getCaptchaRules');

        $parsed = [];

        foreach ($rules as $alias => $values) {
            foreach ($values as $value) {
                $key = $alias . '.' . $value;

                Arr::set($parsed, $key, (bool) Settings::get('captcha.rules.' . $key, false));
            }
        }

        return $parsed;
    }
}
