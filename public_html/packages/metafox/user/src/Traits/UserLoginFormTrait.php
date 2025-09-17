<?php

namespace MetaFox\User\Traits;

use MetaFox\Platform\Facades\Settings;

trait UserLoginFormTrait
{
    protected function getLoginFieldLabel(): string
    {
        if (Settings::get('user.enable_phone_number_registration')) {
            return __p('user::phrase.username_email_or_phone_number');
        }

        return __p('user::phrase.username_or_email');
    }

    protected function getLoginFieldPlaceholder(): string
    {
        if (Settings::get('user.enable_phone_number_registration')) {
            return __p('user::phrase.enter_your_username_email_or_phone_number');
        }

        return __p('user::phrase.enter_your_username_or_email');
    }
}
