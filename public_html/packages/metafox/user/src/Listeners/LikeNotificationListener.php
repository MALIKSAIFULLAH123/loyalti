<?php

namespace MetaFox\User\Listeners;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\User as Model;

/**
 * Class UserPreviewListener.
 * @ignore
 */
class LikeNotificationListener
{
    /**
     * @param  User         $context
     * @param  Content|null $resource
     * @return bool|null
     */
    public function handle(User $context, ?Content $resource): ?bool
    {
        if ($resource === null) {
            return null;
        }

        if (!$context instanceof Model) {
            return null;
        }

        $pass = app('events')->dispatch('user.like_notification', [$context, $resource], true);

        if (null === $pass) {
            return true;
        }

        return $pass;
    }
}
