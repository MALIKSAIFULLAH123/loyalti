<?php

namespace MetaFox\Form\Mobile;

/**
 * Class TiktokLoginButtonField.
 *
 * @driverType form-field-mobile
 * @driverType tiktokLoginButton
 */
class TiktokLoginButtonField extends SubmitButton
{
    public const COMPONENT = 'LoginByTiktokButton';

    public function initialize(): void
    {
        $this->name('tiktok')
            ->setComponent(self::COMPONENT)
            ->marginNone()
            ->sizeNormal()
            ->variant('standard')
            ->type('submit')
            ->color('primary')
            ->label(__p('user::phrase.sign_in_with_tiktok'))
            ->fullWidth();
    }
}
