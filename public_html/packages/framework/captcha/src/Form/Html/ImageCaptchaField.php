<?php

namespace MetaFox\Captcha\Form\Html;

use MetaFox\Captcha\Support\CaptchaSupport;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Yup\Yup;

class ImageCaptchaField extends AbstractCaptchaField
{
    public function initialize(): void
    {
        $this->name('captcha')
            ->setComponent(MetaFoxForm::IMAGE_CAPTCHA_FIELD)
            ->marginNormal()
            ->sizeMedium()
            ->variant('standard')
            ->label(__p('captcha::phrase.captcha'))
            ->required()
            ->yup(
                Yup::string()
                    ->required()
            );
    }

    /**
     * @param  string|null $base64
     * @return $this
     */
    public function img(?string $base64): self
    {
        return $this->setAttribute('img', $base64);
    }

    /**
     * @param  string|null $publicKey
     * @return $this
     */
    public function publicKey(?string $publicKey): self
    {
        return $this->setAttribute('key', $publicKey);
    }

    /**
     * @param  bool  $value
     * @return $this
     */
    public function sensitive(bool $value): self
    {
        return $this->setAttribute('sensitive', $value);
    }

    public function toType(): string
    {
        return CaptchaSupport::IMAGE_CAPTCHA_TYPE;
    }

    public function toConfiguration(): array
    {
        return [
            'img'       => $this->getAttribute('img'),
            'key'       => $this->getAttribute('key'),
            'sensitive' => $this->getAttribute('sensitive', false),
        ];
    }

    public function toTokenAction(): string
    {
        return 'captcha/image_captcha/token';
    }
}
