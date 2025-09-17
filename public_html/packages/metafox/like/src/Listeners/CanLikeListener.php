<?php

namespace MetaFox\Like\Listeners;

use MetaFox\Like\Policies\LikePolicy;
use MetaFox\Platform\Contracts\User;

class CanLikeListener
{
    public function handle(string $entityType, User $user, $resource, $newValue = null): bool
    {
        return policy_check(LikePolicy::class, 'likeItem', $entityType, $user, $resource, $newValue);
    }
}
