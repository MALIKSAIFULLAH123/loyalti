<?php

namespace MetaFox\Comment\Listeners;

use MetaFox\Comment\Models\Comment;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * Class UserPreviewListener.
 * @ignore
 */
class LikeNotificationListener
{
    /**
     * @param  User|null    $context
     * @param  Content|null $resource
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

        if (!$resource instanceof Comment) {
            return null;
        }

        return app('events')->dispatch('comment.owner.notification', [$context, $resource->item], true);
    }
}
