<?php

namespace MetaFox\Advertise\Policies\Handlers;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanSponsor extends BaseSponsorHandler implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if (!$this->validateCreatePermission($user)) {
            return false;
        }

        if (!$this->validateResourceStatus($resource)) {
            return false;
        }

        if (!$this->validatePermissionOnResource($user, $resource)) {
            return false;
        }

        if (!$user->hasPermissionTo(sprintf('%s.%s', $entityType, 'sponsor'))) {
            return false;
        }

        if (!$user->hasPermissionTo(sprintf('%s.%s', $entityType, 'sponsor_free'))) {
            return false;
        }

        return $this->validateValue($resource, 1);
    }
}
