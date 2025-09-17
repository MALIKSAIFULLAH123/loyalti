<?php

namespace MetaFox\Captcha\Support;

use Illuminate\Support\Arr;
use MetaFox\Captcha\Contracts\CaptchaContract;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use Mews\Captcha\Facades\Captcha;

class ImageCaptcha implements CaptchaContract
{
    public const DEFAULT_CONFIG = 'flat';

    public const KEY_NAME = 'image_captcha_key';

    public function verify(string $token, ?string $action = null, ?string $publicKey = null): bool
    {
        if (null !== $action) {
            if (!Settings::get('captcha.rules.' . $action)) {
                return false;
            }
        }

        if (null === $publicKey) {
            return false;
        }

        return captcha_api_check($token, $publicKey, self::DEFAULT_CONFIG);
    }

    public function ruleOf(?string $action = null): array
    {
        if (null !== $action) {
            if (!Settings::get('captcha.rules.' . $action)) {
                return ['sometimes', 'nullable'];
            }
        }

        return ['required', 'captcha_api:' . request(self::KEY_NAME) . ',' . self::DEFAULT_CONFIG];
    }

    public function errorMessage(?string $action = null): string
    {
        return __p('validation.image_captcha');
    }

    public function ruleMessage(?string $action = null, string $name = 'captcha'): array
    {
        $message = __p('validation.image_captcha');

        return [
            $name . '.required'    => $message,
            $name . '.captcha_api' => $message,
        ];
    }

    public function generateFormField(?string $action = null, string $resolution = 'web', bool $isPreload = false, string $fieldName = 'captcha', bool $isHidden = true): ?AbstractField
    {
        $img = $key = null;

        $sensitive = false;

        if (!$isPreload && !$isHidden) {
            $data = Captcha::create(self::DEFAULT_CONFIG, true);

            $img = Arr::get($data, 'img', MetaFoxConstant::EMPTY_STRING);

            $sensitive = Arr::get($data, 'sensitive', false);

            $key = Arr::get($data, 'key', MetaFoxConstant::EMPTY_STRING);
        }

        $name = match ($isHidden) {
            true  => 'hiddenImageCaptcha',
            false => 'imageCaptcha'
        };

        $driver = resolve(DriverRepositoryInterface::class)->getDriver(Constants::DRIVER_TYPE_FORM_FIELD, $name, $resolution);

        $field = resolve($driver);

        return $field->name($fieldName)
            ->actionName($action)
            ->img($img)
            ->sensitive($sensitive)
            ->publicKey($key);
    }

    public function refresh(): array
    {
        $data = Captcha::create(self::DEFAULT_CONFIG, true);

        if (!is_array($data)) {
            return [];
        }

        return $data;
    }
}
