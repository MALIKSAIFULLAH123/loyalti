<?php

namespace MetaFox\Photo\Policies;

use MetaFox\Photo\Models\Photo;
use MetaFox\Platform\Contracts\TagFriendModel;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * @SuppressWarnings(PHPMD)
 */
class PhotoTagFriendPolicy
{
    use HasPolicyTrait;

    public function removeTaggedFriend(User $context, TagFriendModel $resource): bool
    {
        $item = $resource->item;
        if (!$item instanceof Photo) {
            return false;
        }

        if ($context->hasPermissionTo('photo.moderate')) {
            return true;
        }

        $isTagger = $context->entityId() == $resource->userId();
        $isTagged = $context->entityId() == $resource->ownerId();
        $isItemOwner = $context->entityId() == $item->userId();

        return $isTagger || $isTagged || $isItemOwner;
    }
}
