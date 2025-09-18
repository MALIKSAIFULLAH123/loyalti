<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Support\GroupPrivacy;
use MetaFox\User\Contracts\Support\PrivacyForSettingInterface;

/**
 * Class PrivacyForSetting.
 * @ignore
 */
class PrivacyForSetting
{
    /**
     * @return PrivacyForSettingInterface
     */
    public function handle(): PrivacyForSettingInterface
    {
        return resolve(GroupPrivacy::class);
    }
}
