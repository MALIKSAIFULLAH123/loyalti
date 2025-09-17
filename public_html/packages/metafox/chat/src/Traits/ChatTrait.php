<?php

namespace MetaFox\Chat\Traits;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\User\Support\Facades\UserBlocked;

trait ChatTrait
{
    public function canMessage(?User $context, ?User $user): bool
    {
        $isAppActive = app_active('metafox/chat');
        $ownerId     = $context instanceof User ? $context->getAuthIdentifier() : null;
        $userId      = $user instanceof User ? $user->getAuthIdentifier() : null;

        if (!$isAppActive) {
            return false;
        }

        if (!Settings::get('broadcast.connections.pusher.key')) {
            return false;
        }

        if (!$context instanceof User || !$user instanceof User) {
            return false;
        }

        if ($context->isGuest() || $user->entityId() == $context->entityId()) {
            return false;
        }

        if (UserBlocked::isBlocked($context, $user) || UserBlocked::isBlocked($user, $context)) {
            return false;
        }

        if ($ownerId && $userId && $ownerId != $userId &&
            !app('events')->dispatch('friend.is_friend', [$context->entityId(), $user->entityId()], true)) {
            return false;
        }

        return true;
    }
}
