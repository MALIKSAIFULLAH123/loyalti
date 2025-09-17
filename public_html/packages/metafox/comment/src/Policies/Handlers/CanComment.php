<?php

namespace MetaFox\Comment\Policies\Handlers;

use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

class CanComment implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        return policy_check(CommentPolicy::class, 'commentItem', $entityType, $user, $resource, $newValue);
    }
}
