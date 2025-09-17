<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Database\Query\JoinClause;
use MetaFox\Friend\Models\Friend;
use MetaFox\Friend\Policies\FriendPolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;

class FilterTagFriendByMultipleUsersListener
{
    public function handle(User $context, User $user, array $ownerIds): array
    {
        if (!policy_check(FriendPolicy::class, 'viewAny', $context, $user)) {
            return [];
        }

        if (!count($ownerIds)) {
            return [];
        }

        return array_unique(Friend::query()
            ->leftJoin('user_privacy_values as can_be_tagged', function (JoinClause $join) {
                $join->on('friends.owner_id', '=', 'can_be_tagged.user_id');
                $join->where('can_be_tagged.name', '=', 'user:can_i_be_tagged');
                $join->where('can_be_tagged.privacy', '=', MetaFoxPrivacy::ONLY_ME);
            })
            ->whereIn('friends.owner_id', $ownerIds)
            ->whereNull('can_be_tagged.id')
            ->where('friends.user_id', $user->entityId())
            ->select(['friends.owner_id'])
            ->pluck('owner_id')
            ->toArray());
    }
}
