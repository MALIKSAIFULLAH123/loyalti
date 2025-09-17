<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Support\Facades\Group as Facade;
use MetaFox\Group\Support\Facades\GroupMember as MemberFacade;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;

class FriendMemberMentionBuilderListener
{
    public function handle(?User $context, User $user, User $group, array $attributes): ?array
    {
        if ($group->entityType() != Group::ENTITY_TYPE) {
            return null;
        }

        if (!policy_check(GroupPolicy::class, 'view', $context, $group)) {
            return null;
        }

        if ($group->privacy_type == PrivacyTypeHandler::PUBLIC) {
            return app('events')->dispatch('friend.mention.builder', [$context, $user, $attributes]);
        }

        $memberBuilder = MemberFacade::getMemberBuilder($user, $group);

        if ($group->privacy_type == PrivacyTypeHandler::SECRET) {
            return [$memberBuilder];
        }

        $groupBuilder = Facade::getGroupBuilder($user);

        return [$memberBuilder, $groupBuilder];
    }
}
