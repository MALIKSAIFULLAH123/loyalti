<?php

namespace MetaFox\User\Listeners;

use MetaFox\User\Contracts\Support\PrivacyForSettingInterface;
use MetaFox\User\Support\UserPrivacy;

class PrivacyForSetting
{
    /**
     * @return PrivacyForSettingInterface
     */
    public function handle(): PrivacyForSettingInterface
    {
        return resolve(UserPrivacy::class);
    }
}
