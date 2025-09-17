<?php

namespace MetaFox\Event\Support;

use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Contracts\Support\PrivacyForSettingInterface;

class Privacy implements PrivacyForSettingInterface
{
    public function getDefaultPrivacy(): int
    {
        // must return EVERYONE,
        // otherwise all un-configured privacies will not pass the hasPermission check
        return MetaFoxPrivacy::EVERYONE;
    }

    public function getPrivacyOptionsPhrase(): array
    {
        return [
            MetaFoxPrivacy::EVERYONE => 'phrase.user_privacy.anyone',
            MetaFoxPrivacy::CUSTOM   => 'event::phrase.user_privacy.co_host',
            MetaFoxPrivacy::FRIENDS  => 'event::phrase.user_privacy.member',
            MetaFoxPrivacy::ONLY_ME  => 'phrase.user_privacy.no_one',
        ];
    }
}
