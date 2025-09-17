<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Policies\FriendPolicy;
use MetaFox\Friend\Policies\FriendRequestPolicy;
use MetaFox\Friend\Support\Facades\Friend as FriendFacade;
use MetaFox\Friend\Support\Friend as FriendSupport;
use MetaFox\Platform\Contracts\User;

/**
 * Class CanAddFriendListener.
 * @ignore
 * @codeCoverageIgnore
 */
class CanAddFriendListener
{
    public function handle(?User $context, ?User $owner): bool
    {
        if (!$context || !$owner) {
            return false;
        }

        $friendRequestPolicy = resolve(FriendRequestPolicy::class);

        $friendPolicy = resolve(FriendPolicy::class);

        if (!$friendRequestPolicy->sendRequest($context, $owner)) {
            return false;
        }

        if (!$friendPolicy->addFriend($context, $owner)) {
            return false;
        }

        $friendship = FriendFacade::getFriendship($context, $owner);

        $accept = $this->getAcceptableStatuses();

        if (!in_array($friendship, $accept)) {
            return false;
        }

        return true;
    }

    protected function getAcceptableStatuses(): array
    {
        return [
            FriendSupport::FRIENDSHIP_CAN_ADD_FRIEND,
            FriendSupport::FRIENDSHIP_CONFIRM_AWAIT,
            FriendSupport::FRIENDSHIP_IS_DENY_REQUEST,
        ];
    }
}
