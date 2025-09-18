<?php

namespace MetaFox\Page\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\Contracts\User;

class FriendMentionTransformAfterListener
{
    public function handle(User $owner, array $data, ?User $user = null): ?array
    {
        /**
         * @var Page $owner
         */
        if ($owner->entityType() !== Page::ENTITY_TYPE) {
            return null;
        }

        if (null === $user) {
            return $data;
        }

        if ($user->entityId() != $owner->entityId()) {
            return $data;
        }

        return array_map(function ($item) {
            $resourceName = Arr::get($item, 'resource_name');

            if (!$resourceName || $resourceName != \MetaFox\User\Models\User::ENTITY_TYPE) {
                return $item;
            }

            return array_merge($item, [
                'type'      => null,
                'statistic' => [
                    'total_people' => 0,
                ],
            ]);
        }, $data);
    }
}
