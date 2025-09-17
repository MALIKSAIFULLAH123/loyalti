<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Models\Group;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Support\Facades\UserBlocked;

class FriendMentionNotifiableListener
{
    public function handle(?User $context, User $owner): ?array
    {
        if ($owner->entityType() != Group::ENTITY_TYPE) {
            return null;
        }

        if (!$owner->isPublicPrivacy()) {
            return [];
        }

        $admins = $owner->admins;

        if (!$admins->count()) {
            return [];
        }

        $notifiables = [];

        foreach ($admins as $admin) {
            if ($admin->userId() == $context->entityId()) {
                continue;
            }

            $user = $admin->user;

            if (UserBlocked::isBlocked($context, $user)) {
                continue;
            }

            if (UserBlocked::isBlocked($user, $context)) {
                continue;
            }

            $notifiables[] = $user;
        }

        return $notifiables;
    }
}
