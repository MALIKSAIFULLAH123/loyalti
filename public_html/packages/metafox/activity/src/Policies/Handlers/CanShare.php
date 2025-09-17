<?php

namespace MetaFox\Activity\Policies\Handlers;

use MetaFox\Activity\Policies\SharePolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanShare implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, mixed $newValue = null): bool
    {
        return policy_check(SharePolicy::class, 'share', $entityType, $user, $resource, $newValue);
    }
}
