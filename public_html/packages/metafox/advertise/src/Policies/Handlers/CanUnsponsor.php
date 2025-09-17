<?php

namespace MetaFox\Advertise\Policies\Handlers;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanUnsponsor extends BaseSponsorHandler implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if ($newValue != 0) {
            return false;
        }

        if (!$resource instanceof Content) {
            return false;
        }

        if (!$this->validateValue($resource, 0)) {
            return false;
        }

        if (!$user->hasPermissionTo(sprintf('%s.%s', $entityType, 'sponsor'))) {
            return false;
        }

        if ($user->hasPermissionTo(sprintf('%s.%s', $entityType, 'sponsor_free'))) {
            return true;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        return false;
    }
}
