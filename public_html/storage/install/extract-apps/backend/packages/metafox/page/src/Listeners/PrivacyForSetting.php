<?php

namespace MetaFox\Page\Listeners;

use MetaFox\Page\Support\PagePrivacy;
use MetaFox\User\Contracts\Support\PrivacyForSettingInterface;

class PrivacyForSetting
{
    /**
     * @return PrivacyForSettingInterface
     */
    public function handle(): PrivacyForSettingInterface
    {
        return resolve(PagePrivacy::class);
    }
}
