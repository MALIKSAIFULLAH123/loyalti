<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Friend\Repositories\FriendRepositoryInterface;
use MetaFox\Friend\Repositories\FriendRequestRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class MakeInvitedUserFriends
{
    public function __construct(
        protected FriendRequestRepositoryInterface $requestRepository,
        protected FriendRepositoryInterface $friendRepository
    ) {
    }

    /**
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function handle(User $user, User $owner): void
    {
        if ($this->friendRepository->isFriend($owner->entityId(), $user->entityId())) {
            return;
        }

        $this->requestRepository->sendRequest($owner, $user);
        $this->friendRepository->addFriend($owner, $user, true);
    }
}
