<?php

namespace MetaFox\Page\Support;

use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Contracts\Support\PrivacyForSettingInterface;

class PagePrivacy implements PrivacyForSettingInterface
{
    public function getDefaultPrivacy(): int
    {
        return Settings::get('page.default_item_privacy', MetaFoxPrivacy::FRIENDS);
    }

    public function getPrivacyOptionsPhrase(): array
    {
        return [
            MetaFoxPrivacy::EVERYONE => 'phrase.user_privacy.anyone',
            MetaFoxPrivacy::FRIENDS  => 'phrase.user_privacy.members_only',
            MetaFoxPrivacy::CUSTOM   => 'phrase.user_privacy.admins_only',
        ];
    }
}
