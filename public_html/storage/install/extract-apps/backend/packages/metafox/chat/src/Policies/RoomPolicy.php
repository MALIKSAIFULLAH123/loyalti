<?php

namespace MetaFox\Chat\Policies;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

class RoomPolicy
{
    use HasPolicyTrait;

    public function view(User $user, ?Entity $room): bool
    {
        $subscriptionUserIds = $room->subscriptions->pluck('user_id')->toArray();
        if (in_array($user->entityId(), $subscriptionUserIds)) {
            return true;
        }

        return false;
    }

    public function delete(User $user, ?Entity $room = null): bool
    {
        if (!$user->hasPermissionTo('chat_room.delete')) {
            return false;
        }

        if (!$room instanceof Entity) {
            return false;
        }

        $subscriptionUserIds = $room->subscriptions->pluck('user_id')->toArray();
        if (!in_array($user->entityId(), $subscriptionUserIds)) {
            return false;
        }

        return true;
    }
}
