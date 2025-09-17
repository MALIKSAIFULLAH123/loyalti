<?php

namespace MetaFox\Event\Listeners;

use MetaFox\Event\Support\Privacy;
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
        return resolve(Privacy::class);
    }
}
