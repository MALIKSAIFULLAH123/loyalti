<?php

namespace MetaFox\Notification\Listeners;

use MetaFox\Notification\Repositories\NotificationRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use StdClass;

/**
 * Class GetNewNotificationCount.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class GetNewNotificationCount
{
    /**
     * @param  User     $user
     * @param  StdClass $data
     * @return void
     */
    public function handle(User $user, StdClass $data): void
    {
        resolve(NotificationRepositoryInterface::class)
            ->getNewNotificationCount($user, $data);
    }
}
