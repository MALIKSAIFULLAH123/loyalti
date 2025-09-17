<?php

namespace MetaFox\User\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\User\Support\Facades\UserValue;

class CheckUserValueSettingByNameListener
{
    public function handle(User $user, string $settingName)
    {
        return UserValue::checkUserValueSettingByName($user, $settingName);
    }
}
