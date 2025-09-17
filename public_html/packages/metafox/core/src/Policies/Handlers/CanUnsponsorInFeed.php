<?php

namespace MetaFox\Core\Policies\Handlers;

use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanUnsponsorInFeed implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        return false;
    }
}
