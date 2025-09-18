<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Models\Group;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Support\Facades\GroupMember as MemberFacade;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\Contracts\User;

class FriendInviteMemberBuilderListener
{
    public function handle(?User $context, User $user, User $group): ?array
    {
        if ($group->entityType() != Group::ENTITY_TYPE) {
            return null;
        }

        if (!policy_check(GroupPolicy::class, 'view', $context, $group)) {
            return null;
        }

        if ($group->privacy_type == PrivacyTypeHandler::PUBLIC) {
            $attributes = [
                'is_mention' => true,
            ];

            $friendBuilder = app('events')->dispatch('friend.mention.builder', [$context, $user, $attributes], true);

            if (null === $friendBuilder) {
                return null;
            }

            return [$friendBuilder];
        }

        $memberBuilder = MemberFacade::getMemberBuilder($user, $group);

        return [$memberBuilder];
    }
}
