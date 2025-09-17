<?php

namespace MetaFox\Chat\Listeners;

use MetaFox\Chat\Repositories\SubscriptionRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UnBlockUserListener
{
    public function handle(User $user, User $owner)
    {
        resolve(SubscriptionRepositoryInterface::class)->handleBlockAction($user, $owner, 0);
    }
}
