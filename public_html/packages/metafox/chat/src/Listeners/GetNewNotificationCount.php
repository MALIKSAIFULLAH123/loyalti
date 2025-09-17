<?php

namespace MetaFox\Chat\Listeners;

use MetaFox\Chat\Repositories\SubscriptionRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use StdClass;

class GetNewNotificationCount
{
    /**
     * @param  User     $user
     * @param  StdClass $data
     * @return void
     */
    public function handle(User $user, StdClass $data)
    {
        resolve(SubscriptionRepositoryInterface::class)
            ->getNewNotificationCount($user, $data);
    }
}
