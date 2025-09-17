<?php

namespace MetaFox\User\Repositories\Eloquent\UserPrivacyTraits;

use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Contracts\Support\PrivacyForSettingInterface;

trait UserPrivacyOptions
{
    /**
     * @param string $entityType
     *
     * @return array<int, mixed>
     */
    public function getUserPrivacyOptions(string $entityType): array
    {
        $privacyList = [];
        /** @var PrivacyForSettingInterface $privacyForSetting */
        $privacyForSetting = app('events')->dispatch("{$entityType}.get_privacy_for_setting", [], true);

        if (!empty($privacyForSetting)) {
            $privacyList = $privacyForSetting->getPrivacyOptionsPhrase();
        }

        $options = [];

        foreach ($privacyList as $privacyValue => $phrase) {
            $options[$privacyValue] = [
                'label'  => __p($phrase),
                'value'  => $privacyValue,
            ];
        }

        return $options;
    }

    /**
     * @param string $entityType
     *
     * @return int
     */
    public function getDefaultPrivacy(string $entityType): int
    {
        $privacyDefault = MetaFoxPrivacy::EVERYONE;
        /** @var PrivacyForSettingInterface $privacyForSetting */
        $privacyForSetting = app('events')->dispatch("{$entityType}.get_privacy_for_setting", [], true);

        if (!empty($privacyForSetting)) {
            $privacyDefault = $privacyForSetting->getDefaultPrivacy();
        }

        return $privacyDefault;
    }
}
