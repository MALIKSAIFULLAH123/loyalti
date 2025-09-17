<?php

namespace MetaFox\Forum\Listeners;

use MetaFox\Forum\Policies\ForumThreadPolicy;
use MetaFox\Platform\Contracts\User;

class CheckIntegrationPermissionListener
{
    public function handle(?User $context, ?User $owner, string $parentType)
    {
        policy_authorize(ForumThreadPolicy::class, 'attachPoll', $context);
    }
}
