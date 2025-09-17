<?php

namespace MetaFox\Report\Policies\Handlers;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanReportItem implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if (!$resource instanceof Entity) {
            return false;
        }

        if (!$user->hasPermissionTo("$entityType.report")) {
            return false;
        }

        if ($resource instanceof Content) {
            if ($resource->isDraft()) {
                return false;
            }

            if (!$resource->isApproved()) {
                return false;
            }

            if ($resource->userId() == $user->entityId()) {
                return false;
            }
        }

        if ($resource instanceof User) {
            if ($resource->entityId() == $user->entityId()) {
                return false;
            }
        }

        return true;
    }
}
