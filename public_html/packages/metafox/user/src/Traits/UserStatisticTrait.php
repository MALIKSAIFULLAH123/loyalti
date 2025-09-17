<?php

namespace MetaFox\User\Traits;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User as ContractsUser;
use MetaFox\Platform\Contracts\UserEntity;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * @property User $resource
 */
trait UserStatisticTrait
{
    use IsFriendTrait;

    /**
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    protected function getStatistic(): array
    {
        $context = user();

        $owner = $this->resource;
        $data  = [];

        if ($owner instanceof UserEntity) {
            $owner = $owner->detail;
        }

        if (!$owner instanceof ContractsUser) {
            return $data;
        }

        if (!UserPrivacy::hasAccess($context, $owner, 'profile.view_profile')) {
            return $data;
        }

        if (UserPrivacy::hasAccess($context, $owner, 'friend:view_friend')) {
            Arr::set($data, 'total_friend', $this->resource->total_friend);
            Arr::set($data, 'total_mutual', $this->countTotalMutualFriend($context->entityId(), $this->resource->entityId()));
            Arr::set($data, 'total_follower', $this->resource->total_follower);
            Arr::set($data, 'total_following', $this->resource->total_following);

            return $data;
        }

        return $data;
    }
}
