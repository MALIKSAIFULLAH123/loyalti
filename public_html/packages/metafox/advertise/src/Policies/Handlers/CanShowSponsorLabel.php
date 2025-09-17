<?php

namespace MetaFox\Advertise\Policies\Handlers;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanShowSponsorLabel implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        if (!$user->hasPermissionTo('advertise_sponsor.view')) {
            return false;
        }

        return true;
    }
}
