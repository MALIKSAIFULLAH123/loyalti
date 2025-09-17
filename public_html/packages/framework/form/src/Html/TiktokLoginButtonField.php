<?php

namespace MetaFox\Form\Html;

use MetaFox\Form\AbstractField;

/**
 * Class TiktokLoginButtonField.
 *
 * @driverType form-field-mobile
 * @driverType tiktokLoginButton
 */
class TiktokLoginButtonField extends AbstractField
{
    public const COMPONENT = 'LoginByTikTokButton';

    public function initialize(): void
    {
        $this->name('tiktok')
            ->setComponent(self::COMPONENT)
            ->label(__p('user::phrase.sign_in_with_tiktok'))
            ->variant('outlined')
            ->setAttribute('color', 'secondary')
            ->setAttribute('icon', app('asset')->findByName('socialite_tiktok')?->url)
            ->fullWidth(false)
            ->sx([
                'flex' => 1,
            ]);
    }
}
