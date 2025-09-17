<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Database\Query\Builder;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Support\Facades\Group as Facade;
use MetaFox\Platform\Contracts\User;

class FriendMentionBuilderListener
{
    public function handle(?User $context, User $user): ?Builder
    {
        if (!policy_check(GroupPolicy::class, 'viewAny', $context, $user)) {
            return null;
        }

        return Facade::getGroupBuilder($user);
    }
}
