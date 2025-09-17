<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;

/**
 * Class FacebookLoginButtonField.
 *
 * @driverType form-field-mobile
 * @driverType facebookLoginButton
 */
class FacebookLoginButtonField extends AbstractField
{
    public const COMPONENT = 'LoginByFacebookButton';

    public function initialize(): void
    {
        $this->name('facebook')
            ->setComponent(self::COMPONENT)
            ->label(__p('user::phrase.sign_in_with_facebook'))
            ->variant('contained')
            ->setAttribute('color', 'primary')
            ->setAttribute('icon', app('asset')->findByName('socialite_facebook')?->url)
            ->fullWidth(false)
            ->sx([
                'flex' => 1,
            ]);
    }
}
