<?php

namespace MetaFox\Photo\Support\Traits;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Photo\Policies\PhotoTagFriendPolicy;
use MetaFox\Photo\Policies\PhotoTaggedFriendPolicy;
use MetaFox\Photo\Support\ResourcePermission;
use MetaFox\Platform\Contracts\Content;

/**
 * Trait HasExtra.
 * @property Content $resource
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
trait PhotoTagFriendHasExtra
{
    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    protected function getTagFriendExtra(): array
    {
        $context = user();
        $policy  = new PhotoTagFriendPolicy();

        return [
            ResourcePermission::CAN_REMOVE_TAGGED_FRIEND => $policy->removeTaggedFriend($context, $this->resource),
        ];
    }
}
