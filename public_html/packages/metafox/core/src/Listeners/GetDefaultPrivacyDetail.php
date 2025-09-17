<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;

class GetDefaultPrivacyDetail
{
    public function handle(int $privacy, ?User $context = null, ?User $owner = null)
    {
        $tooltip = match ($privacy) {
            MetaFoxPrivacy::EVERYONE           => __p('core::phrase.privacy_public'),
            MetaFoxPrivacy::MEMBERS            => __p('core::phrase.privacy_members'),
            MetaFoxPrivacy::FRIENDS            => $this->getFriendPrivacyLabel($context, $owner),
            MetaFoxPrivacy::FRIENDS_OF_FRIENDS => __p('core::phrase.privacy_friends_of_friends'),
            MetaFoxPrivacy::ONLY_ME            => __p('core::phrase.privacy_only_me'),
            MetaFoxPrivacy::CUSTOM             => __p('core::phrase.privacy_custom'),
        };

        return [
            'privacy_icon' => $privacy,
            'tooltip'      => $tooltip,
            'label'        => $tooltip,
        ];
    }

    protected function getFriendPrivacyLabel(?User $context = null, ?User $owner = null): string
    {
        if (null == $context) {
            return __p('core::phrase.privacy_friends');
        }

        if (null == $owner) {
            return __p('core::phrase.privacy_friends');
        }

        if ($context->entityId() == $owner->entityId()) {
            return __p('core::phrase.privacy_friends');
        }

        return __p('core::phrase.privacy_owner_friend', [
            'name' => $owner->toTitle(),
        ]);
    }
}
