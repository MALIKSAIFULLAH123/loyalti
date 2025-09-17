<?php

namespace MetaFox\User\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Models\UserEntity;

class FriendMentionExtraInfoListener
{
    public function handle(User $context, UserEntity $userEntity): ?array
    {
        if ($userEntity->entityType() != Model::ENTITY_TYPE) {
            return null;
        }

        return [
            'type'         => __p('core::web.friend'),
            'total_people' => app('events')->dispatch('friend.count_total_mutual_friend', [$context->entityId(), $userEntity->entityId()], true),
        ];
    }
}
