<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Listeners;

use Illuminate\Support\Facades\Notification;
use MetaFox\Platform\Contracts\Content;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Notifications\NewPostTimeline;

/**
 * Class FeedNotificationCreatedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class FeedComposerNotificationListener
{
    /**
     * @param  UserEntity   $user
     * @param  UserEntity   $owner
     * @param  Content|null $item
     * @return void
     */
    public function handle(UserEntity $user, UserEntity $owner, ?Content $item = null): void
    {
        if ($user->entityId() == $owner->entityId()) {
            return;
        }

        if (!$owner->detail instanceof User) {
            return;
        }

        Notification::send($owner->detail, new NewPostTimeline($item));
    }
}
