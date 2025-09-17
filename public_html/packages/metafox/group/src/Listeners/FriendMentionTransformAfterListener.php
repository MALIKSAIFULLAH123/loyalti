<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Group\Models\Group;
use MetaFox\Platform\Contracts\User;

class FriendMentionTransformAfterListener
{
    public function handle(User $owner, array $data, ?User $user = null): ?array
    {
        /**
         * @var Group $owner
         */
        if ($owner->entityType() !== Group::ENTITY_TYPE) {
            return null;
        }

        if ($owner->isPublicPrivacy()) {
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
