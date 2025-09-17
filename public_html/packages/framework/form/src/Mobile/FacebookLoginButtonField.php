<?php

namespace MetaFox\Form\Mobile;

/**
 * Class FacebookLoginButtonField.
 *
 * @driverType form-field-mobile
 * @driverType facebookLoginButton
 */
class FacebookLoginButtonField extends SubmitButton
{
    public const COMPONENT = 'LoginByFacebookButton';

    public function initialize(): void
    {
        $this->name('facebook')
            ->setComponent(self::COMPONENT)
            ->marginNone()
            ->sizeNormal()
            ->variant('standard')
            ->type('submit')
            ->color('primary')
            ->label(__p('user::phrase.sign_in_with_facebook'))
            ->fullWidth();
    }
}
