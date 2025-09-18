<?php

namespace MetaFox\Page\Listeners;

use Illuminate\Support\Collection;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Platform\Contracts\User;

class FilterMentionUsersListener
{
    public function handle(User $context, User $user, Collection $userEntities): array
    {
        $pages = $userEntities->filter(function ($entity) {
            return $entity->entity_type == Page::ENTITY_TYPE;
        });

        if (!$pages->count()) {
            return [];
        }

        $pageIds = $pages->pluck('id')->toArray();

        if (!policy_check(PagePolicy::class, 'viewAny', $context)) {
            return [];
        }

        $builder = \MetaFox\Page\Support\Facade\Page::getPageBuilder($user);

        return $builder->whereIn('pages.id', $pageIds)
            ->pluck('id')
            ->toArray();
    }
}
