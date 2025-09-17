<?php

namespace MetaFox\Follow\Listeners;

use MetaFox\Follow\Repositories\FollowRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * Class UserBlockedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class UserBlockedListener
{
    protected function repository(): FollowRepositoryInterface
    {
        return resolve(FollowRepositoryInterface::class);
    }

    /**
     * @param User $user
     * @param User $owner
     */
    public function handle(User $user, User $owner): void
    {
        $isFollow = $this->repository()->isFollow($user->entityId(), $owner->entityId());
        if (!$isFollow) {
            return;
        }
        $this->repository()->unfollow($user, $owner);
    }
}
