<?php

namespace MetaFox\Comment\Listeners;

use MetaFox\Comment\Policies\CommentPolicy;
use MetaFox\Platform\Contracts\User;

class CanCommentListener
{
    public function handle(string $entityType, User $user, $resource, $newValue = null): bool
    {
        return policy_check(CommentPolicy::class, 'commentItem', $entityType, $user, $resource, $newValue);
    }
}
