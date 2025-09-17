<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Friend\Repositories\FriendRequestRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use StdClass;

/**
 * Class CanAddFriendListener.
 * @ignore
 * @codeCoverageIgnore
 */
class CountNewFriendRequestListener
{
    /**
     * @param  User                   $context
     * @param  StdClass               $data
     * @return void
     * @throws AuthorizationException
     */
    public function handle(?User $context, StdClass $data): void
    {
        if (!$context) {
            return;
        }
        resolve(FriendRequestRepositoryInterface::class)
            ->countFriendRequest($context, $data);
    }
}
