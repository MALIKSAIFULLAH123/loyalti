<?php

namespace MetaFox\Page\Listeners;

use Illuminate\Database\Query\Builder;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Support\Facade\Page;
use MetaFox\Platform\Contracts\User;
use MetaFox\Page\Models\Page as Model;

class FriendMemberMentionBuilderListener
{
    public function handle(User $context, User $user, User $page, array $attributes): ?array
    {
        if (!policy_check(PagePolicy::class, 'viewAny', $context, $user)) {
            return null;
        }

        /*
         * If this event is dispatched with from group/user/event..
         */
        if ($page->entityType() != Model::ENTITY_TYPE) {
            return [Page::getPageBuilder($user)];
        }

        if ($user->entityId() != $page->entityId()) {
            return app('events')->dispatch('friend.mention.builder', [$context, $user, $attributes]);
        }

        $builders = [$this->getPageBuilder($user, $page), Page::getMemberBuilderForLoginAsPage($page)];

        return $this->getPublicGroupBuilder($context, $user, $builders);
    }

    protected function getPageBuilder(User $user, User $page): Builder
    {
        /**
         * @var Builder $pageBuilder
         */
        $pageBuilder = Page::getPageBuilder($user);

        $pageBuilder->where('pages.id', '<>', $page->entityId());

        return $pageBuilder;
    }

    protected function getPublicGroupBuilder(User $context, User $user, array $builders): array
    {
        $groupBuilders = app('events')->dispatch('group.mention.public_group', [$context, $user]);

        if (!is_array($groupBuilders)) {
            return $builders;
        }

        $groupBuilders = array_filter($groupBuilders, function ($item) {
            return $item instanceof Builder || is_array($item);
        });

        foreach ($groupBuilders as $groupBuilder) {
            if ($groupBuilder instanceof Builder) {
                $builders[] = $groupBuilder;
                continue;
            }

            foreach ($groupBuilder as $item) {
                if (!$item instanceof Builder) {
                    continue;
                }

                $builders[] = $item;
            }
        }

        return $builders;
    }
}
