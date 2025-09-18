<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Database\Query\Builder;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Support\Facades\Group;
use MetaFox\Platform\Contracts\User;

class MentionPublicGroupBuilderListener
{
    public function handle(User $context, User $user): ?Builder
    {
        if (!policy_check(GroupPolicy::class, 'viewAny', $context)) {
            return null;
        }

        return Group::getPublicGroupBuilder($user);
    }
}
