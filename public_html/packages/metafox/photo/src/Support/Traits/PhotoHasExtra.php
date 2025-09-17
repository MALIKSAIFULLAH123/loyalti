<?php

namespace MetaFox\Photo\Support\Traits;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Photo\Policies\PhotoPolicy;
use MetaFox\Photo\Support\ResourcePermission;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\ResourcePermission as ACL;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;

/**
 * Trait HasExtra.
 * @property Content $resource
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
trait PhotoHasExtra
{
    use HasExtra;

    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    protected function getCustomExtra(): array
    {
        $extras = $this->getExtra();

        $context = user();

        $policy = new PhotoPolicy();

        return array_merge($extras, [
            ACL::CAN_DOWNLOAD                          => $policy->download($context, $this->resource),
            ResourcePermission::CAN_SET_PROFILE_AVATAR => $policy->setProfileAvatar($context, $this->resource),
            ResourcePermission::CAN_SET_PROFILE_COVER  => $policy->setProfileCover($context, $this->resource),
            ResourcePermission::CAN_SET_PARENT_COVER   => $policy->setParentCover($context, $this->resource),
            ResourcePermission::CAN_SET_PARENT_AVATAR  => $policy->setParentAvatar($context, $this->resource),
            ResourcePermission::CAN_TAG_FRIEND         => $policy->tagFriend($context, null, $this->resource),
            ResourcePermission::CAN_VIEW_MATURE        => $policy->viewMature($context, $this->resource),
            ResourcePermission::CAN_EDIT_MATURE        => $policy->updateMature($context, $this->resource),
        ]);
    }
}
