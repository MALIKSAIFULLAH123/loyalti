<?php

namespace MetaFox\User\Traits;

use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * @property User $resource
 */
trait FriendStatisticTrait
{
    use IsFriendTrait;

    /**
     * @return array<string,           mixed>
     */
    protected function getFriendStatistic(): array
    {
        if (empty($this->resource)) {
            return [];
        }

        $context = user();

        if (!UserPrivacy::hasAccess($context, $this->resource, 'profile.view_profile')) {
            return [];
        }

        if (!UserPrivacy::hasAccess($context, $this->resource, 'friend:view_friend')) {
            return [];
        }

        return [
            'total_friend'  => $this->countTotalFriend($this->resource->entityId()),
            'total_request' => $this->countTotalFriendRequest($this->resource),
        ];
    }
}
