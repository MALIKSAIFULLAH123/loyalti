<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Support\Facades\Friend;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;

/**
 * Class GetFriendShipListener.
 * @ignore
 * @codeCoverageIgnore
 */
class GetFriendShipListener
{
    /**
     * @param  User|null  $context
     * @param  User  $user
     *
     * @return int
     */
    public function handle(?User $context, User $user): int
    {
        if (!$context) {
            return 4;
        }

        return LoadReduce::remember(sprintf("GetFriendShipListener::handle(%s,%s)", $context->id, $user->id),
            fn() => Friend::getFriendship($context, $user));
    }
}
