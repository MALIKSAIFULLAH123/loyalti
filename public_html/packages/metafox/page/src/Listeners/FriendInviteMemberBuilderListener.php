<?php

namespace MetaFox\Page\Listeners;

use MetaFox\Page\Models\Page;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Platform\Contracts\User;
use MetaFox\Page\Support\Facade\Page as Facade;

class FriendInviteMemberBuilderListener
{
    public function handle(?User $context, User $user, User $page, array $attributes): ?array
    {
        if ($page->entityType() != Page::ENTITY_TYPE) {
            return null;
        }

        if (!policy_check(PagePolicy::class, 'view', $context, $page)) {
            return null;
        }

        /*
         * It means login as page
         */
        if ($user->entityId() != $page->entityId()) {
            $attributes = array_merge($attributes, [
                'is_mention' => true,
            ]);

            $friendBuilder = app('events')->dispatch('friend.mention.builder', [$context, $user, $attributes], true);

            if (null === $friendBuilder) {
                return null;
            }

            return [$friendBuilder];
        }

        return [Facade::getMemberBuilderForLoginAsPage($page)];
    }
}
