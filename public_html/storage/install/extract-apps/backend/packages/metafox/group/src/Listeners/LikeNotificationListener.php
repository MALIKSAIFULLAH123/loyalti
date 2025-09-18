<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Models\Group;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * Class UserPreviewListener.
 * @ignore
 */
class LikeNotificationListener
{
    /**
     * @param User|null    $context
     * @param Content|null $resource
     * @return bool|null
     */
    public function handle(?User $context, ?Content $resource): ?bool
    {
        if (!$context) {
            return null;
        }
        if ($resource === null) {
            return null;
        }

        $owner = $resource->owner;

        if (!$owner instanceof Group) {
            return null;
        }

        if ($context instanceof Group) {
            return false;
        }

        if (!$owner->isMember($context) && !$owner->isPublicPrivacy()) {
            return false;
        }

        return true;
    }
}
