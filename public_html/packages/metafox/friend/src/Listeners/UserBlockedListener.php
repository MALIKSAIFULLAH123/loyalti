<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Friend\Repositories\FriendRepositoryInterface;
use MetaFox\Friend\Repositories\FriendRequestRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * Class UserBlockedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class UserBlockedListener
{
    public function __construct(
        protected FriendRepositoryInterface $friendService,
        protected FriendRequestRepositoryInterface $friendRequestService
    ) {
    }

    /**
     * @throws AuthorizationException
     */
    public function handle(User $user, User $owner): void
    {
        $this->friendService->clearFriendSuggestionCache($user->entityId(), $owner->entityId());

        $this->handleRemoveFriendRequest($user, $owner);
        $this->handleUnFriend($user, $owner);
    }

    private function handleUnFriend(User $user, User $owner): void
    {
        $isFriend = $this->friendService->isFriend($user->entityId(), $owner->entityId());

        if ($isFriend) {
            $this->friendService->unFriend($user->entityId(), $owner->entityId());
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function handleRemoveFriendRequest(User $user, User $owner): void
    {
        $hasSentRequest = $this->friendRequestService->isRequested($user->entityId(), $owner->entityId());

        if ($hasSentRequest) {
            $this->friendRequestService->deleteRequestByUserIdAndOwnerId($user, $owner->entityId());
        }

        $hasFriendRequest = $this->friendRequestService->isRequested($owner->entityId(), $user->entityId());

        if ($hasFriendRequest) {
            $this->friendRequestService->deleteRequestByUserIdAndOwnerId($owner, $user->entityId());
        }
    }
}
