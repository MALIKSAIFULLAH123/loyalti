<?php

namespace MetaFox\Captcha\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Captcha\Support\Contracts\CaptchaSupportContract;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Section;

/**
 * class RecaptchaV3.
 * @method static false|mixed verify(string $token, ?string $action = null)
 * @method static null|array ruleOf(?string $action = null)
 * @method static null|string errorMessage(?string $action = null)
 * @method static null|string ruleMessage(?string $action = null, string $name = 'captcha')
 * @method static null|AbstractField getFormField(?string $action = null, string $resolution = 'web', bool $isPreload = false, string $name = 'captcha', bool $isHidden = true)
 * @method static string getDefaultCaptchaType()
 * @method static Section addFormField(Section $basic, ?string $action = null, string $resolution = 'web', bool $isPreload = false)
 * @method static array refresh(?string $action = null)
 * @method static array getRules()
 * @see RecaptchaV3Support
 */
class Captcha extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CaptchaSupportContract::class;
    }
}
