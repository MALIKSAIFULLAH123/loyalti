<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Support\Collection;
use MetaFox\Friend\Policies\FriendPolicy;
use MetaFox\Platform\Contracts\User;

class FilterMentionUsersListener
{
    public function handle(User $context, User $user, Collection $userEntities): array
    {
        $users = $userEntities->filter(function ($entity) {
            return $entity->entity_type == \MetaFox\User\Models\User::ENTITY_TYPE;
        });

        if (!$users->count()) {
            return [];
        }

        $userIds = $users->pluck('id')->toArray();

        if (!policy_check(FriendPolicy::class, 'viewAny', $context, $user)) {
            return [];
        }

        return app('events')->dispatch('friend.filter_tag_friends_by_multiple_users', [$context, $user, $userIds], true) ?: [];
    }
}
