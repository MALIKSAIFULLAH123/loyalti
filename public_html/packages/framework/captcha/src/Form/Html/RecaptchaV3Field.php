<?php

namespace MetaFox\Captcha\Form\Html;

use MetaFox\Captcha\Support\CaptchaSupport;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

class RecaptchaV3Field extends AbstractCaptchaField
{
    public function initialize(): void
    {
        $this->name('captcha')
            ->setComponent(MetaFoxForm::CAPTCHA_FIELD)
            ->marginNormal()
            ->sizeMedium()
            ->variant('standard')
            ->label(__p('core::phrase.permissions'))
            ->required()
            ->siteKey(Settings::get('captcha.recaptcha_v3.site_key', MetaFoxConstant::EMPTY_STRING))
            ->yup(Yup::string()->optional()->nullable());
    }

    /**
     * Set captcha site key.
     *
     * @param string $siteKey
     *
     * @return $this
     */
    public function siteKey(string $siteKey): self
    {
        return $this->setAttribute('siteKey', $siteKey);
    }

    public function toType(): string
    {
        return CaptchaSupport::RECAPTCHA_V3_TYPE;
    }

    public function toConfiguration(): array
    {
        return [
            'siteKey' => $this->getAttribute('siteKey'),
        ];
    }

    public function toTokenAction(): string
    {
        return 'captcha/recaptcha/token';
    }
}
