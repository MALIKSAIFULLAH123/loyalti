<?php

namespace MetaFox\Core\Policies\Handlers;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanPublish implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if (!$resource) {
            return false;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        if ($resource->isPublished()) {
            return false;
        }

        return true;

        if ($user->hasPermissionTo("$entityType.moderate")) {
            return true;
        }

        if (!$user->hasPermissionTo("$entityType.publish")) {
            return false;
        }

        $owner = $resource->owner;

        if ($user->entityId() == $resource->userId()) {
            return true;
        }
        if ($owner instanceof User) {
            if ($owner->entityId() != $user->entityId()) {
                if ($owner instanceof HasPrivacyMember) {
                    if (!$owner->isAdmin($user)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
