<?php

namespace MetaFox\Friend\Support\Traits;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Friend\Models\FriendRequest;
use MetaFox\Friend\Policies\FriendRequestPolicy;
use MetaFox\Platform\ResourcePermission;

/**
 * Trait CommentExtraTrait.
 * @property FriendRequest $resource
 */
trait HasFriendExtraTrait
{
    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    protected function getExtra(): array
    {
        $policy = new FriendRequestPolicy();

        $context = user();

        return [
            ResourcePermission::CAN_DELETE => $policy->delete($context),
        ];
    }
}
