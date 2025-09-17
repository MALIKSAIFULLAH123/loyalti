<?php

namespace MetaFox\Form\Mobile;

/**
 * Class AppleLoginButtonField.
 *
 * @driverType form-field-mobile
 * @driverType appleLoginButton
 */
class AppleLoginButtonField extends SubmitButton
{
    public const COMPONENT = 'LoginByAppleButton';

    public function initialize(): void
    {
        $this->name('apple')
            ->setComponent(self::COMPONENT)
            ->marginNone()
            ->sizeNormal()
            ->variant('standard')
            ->type('submit')
            ->color('black')
            ->label(__p('user::phrase.sign_in_with_apple'))
            ->fullWidth();
    }
}
