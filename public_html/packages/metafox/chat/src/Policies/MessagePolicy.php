<?php

namespace MetaFox\Chat\Policies;

use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\User\Contracts\UserBlockedSupportContract;
use MetaFox\User\Models\User as UserModel;

class MessagePolicy
{
    use HasPolicyTrait;

    public function create(User $user, ?Entity $room): bool
    {
        if (!$user->hasPermissionTo('chat_message.create')) {
            return false;
        }

        $subscriptionUserIds = $room?->subscriptions->pluck('user_id')->toArray();
        if (!in_array($user->entityId(), $subscriptionUserIds)) {
            return false;
        }

        $otherUserId = Arr::first(array_diff($subscriptionUserIds, [$user->entityId()]));
        if (empty($otherUserId)) {
            return false;
        }

        $otherUser   = UserModel::query()->getModel()->find($otherUserId);
        if (empty($otherUser)) {
            return false;
        }
        $userBlock   = resolve(UserBlockedSupportContract::class);

        return !($userBlock->isBlocked($otherUser, $user) || $userBlock->isBlocked($user, $otherUser));
    }

    public function update(User $user, ?Entity $message = null): bool
    {
        if (!$user->hasPermissionTo('chat_message.update')) {
            return false;
        }

        if (!$message instanceof Entity) {
            return false;
        }

        if ($user->entityId() != $message->user_id) {
            return false;
        }

        return true;
    }

    public function delete(?User $user, ?Entity $message = null): bool
    {
        if (!$user->hasPermissionTo('chat_message.delete')) {
            return false;
        }

        if (!$message instanceof Entity) {
            return false;
        }

        if ($user->entityId() != $message->user_id) {
            return false;
        }

        return true;
    }
}
