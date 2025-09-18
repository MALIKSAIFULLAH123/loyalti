<?php

namespace MetaFox\Group\Support;

use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Contracts\Support\PrivacyForSettingInterface;

class GroupPrivacy implements PrivacyForSettingInterface
{
    public function getDefaultPrivacy(): int
    {
        return Settings::get('group.default_item_privacy', MetaFoxPrivacy::FRIENDS);
    }

    public function getPrivacyOptionsPhrase(): array
    {
        return [
            MetaFoxPrivacy::FRIENDS => 'phrase.user_privacy.members_only',
            MetaFoxPrivacy::CUSTOM  => 'phrase.user_privacy.admins_only',
        ];
    }
}
