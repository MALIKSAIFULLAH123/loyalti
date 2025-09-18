<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Support\Collection;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Platform\Contracts\User;

class FilterMentionUsersListener
{
    public function handle(User $context, User $user, Collection $userEntities): array
    {
        $groups = $userEntities->filter(function ($entity) {
            return $entity->entity_type == Group::ENTITY_TYPE;
        });

        if (!$groups->count()) {
            return [];
        }

        $groupIds = $groups->pluck('id')->toArray();

        if (!policy_check(GroupPolicy::class, 'viewAny', $context)) {
            return [];
        }

        $builder = \MetaFox\Group\Support\Facades\Group::getGroupBuilder($user);

        return $builder->whereIn('groups.id', $groupIds)
            ->pluck('id')
            ->toArray();
    }
}
