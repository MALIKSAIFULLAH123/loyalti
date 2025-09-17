<?php

namespace MetaFox\Form\Mobile;

/**
 * Class GoogleLoginButtonField.
 *
 * @driverType form-field-mobile
 * @driverType googleLoginButton
 */
class GoogleLoginButtonField extends SubmitButton
{
    public const COMPONENT = 'LoginByGoogleButton';

    public function initialize(): void
    {
        $this->name('google')
            ->setComponent(self::COMPONENT)
            ->marginNone()
            ->sizeNormal()
            ->variant('standard')
            ->type('submit')
            ->color('primary')
            ->label(__p('user::phrase.sign_in_with_google'))
            ->fullWidth();
    }
}
