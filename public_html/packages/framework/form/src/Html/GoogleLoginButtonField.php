<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;

/**
 * Class GoogleLoginButtonField.
 *
 * @driverType form-field-mobile
 * @driverType googleLoginButton
 */
class GoogleLoginButtonField extends AbstractField
{
    public const COMPONENT = 'LoginByGoogleButton';

    public function initialize(): void
    {
        $this->name('google')
            ->setComponent(self::COMPONENT)
            ->label(__p('user::phrase.sign_in_with_google'))
            ->variant('outlined')
            ->setAttribute('color', 'secondary')
            ->setAttribute('icon', app('asset')->findByName('socialite_google')?->url)
            ->fullWidth(false)
            ->sx([
                'flex' => 1,
            ]);
    }
}
