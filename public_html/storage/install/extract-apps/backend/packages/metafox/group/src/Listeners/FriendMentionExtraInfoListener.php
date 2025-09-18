<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\UserEntity;

class FriendMentionExtraInfoListener
{
    public function handle(?User $context, UserEntity $userEntity): ?array
    {
        if ($userEntity->entityType() != Group::ENTITY_TYPE) {
            return null;
        }

        /** @var Group $group */
        $group = $userEntity->detail;

        if (null === $group) {
            return [];
        }

        return [
            'type'         => __p(PrivacyTypeHandler::PRIVACY_PHRASE[$group->privacy_type]),
            'total_people' => $group->total_member,
        ];
    }
}
