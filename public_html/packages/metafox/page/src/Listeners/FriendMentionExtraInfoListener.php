<?php

namespace MetaFox\Page\Listeners;

use MetaFox\Page\Models\Page;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\UserEntity;

class FriendMentionExtraInfoListener
{
    public function handle(?User $context, UserEntity $userEntity): ?array
    {
        if ($userEntity->entityType() != Page::ENTITY_TYPE) {
            return null;
        }

        $page = $userEntity->detail;

        if (null === $page) {
            return [];
        }

        return [
            'type'         => __p('page::phrase.page'),
            'total_people' => $page->total_member,
        ];
    }
}
