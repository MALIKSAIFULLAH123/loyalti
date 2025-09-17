<?php

namespace MetaFox\Like\Policies\Handlers;

use MetaFox\Like\Policies\LikePolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\PolicyRuleInterface;

/**
 * Class CanLike.
 * @ignore
 * @codeCoverageIgnore
 */
class CanLike implements PolicyRuleInterface
{
    public function check(string $entityType, User $user, $resource, $newValue = null): ?bool
    {
        return policy_check(LikePolicy::class, 'likeItem', $entityType, $user, $resource, $newValue);
    }
}
