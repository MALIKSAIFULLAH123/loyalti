<?php

namespace MetaFox\Core\Policies\Handlers;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanViewApprove implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if (!$resource instanceof HasApprove) {
            return true;
        }

        if ($user->hasSuperAdminRole()) {
            return true;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        $isApproved = $resource->isApproved();

        if (!$isApproved && $user->isGuest()) {
            return false;
        }

        if ($user->hasPermissionTo("$entityType.moderate")) {
            return true;
        }

        if (!$isApproved && !$user->hasPermissionTo("$entityType.approve")) {
            return false;
        }

        return true;
    }
}
