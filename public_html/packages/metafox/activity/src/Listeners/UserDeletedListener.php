<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Repositories\PostRepositoryInterface;
use MetaFox\Activity\Repositories\ShareRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function handle(?User $user): void
    {
        if (!$user) {
            return;
        }

        $this->deleteShares($user);

        $this->deletePosts($user);

        $this->deleteSnoozes($user);
    }

    protected function deleteSnoozes(User $user): void
    {
        /*
         * TODO: delete snooze records of user_id and owner_id
         */
    }

    protected function deletePosts(User $user): void
    {
        $repository = resolve(PostRepositoryInterface::class);

        $repository->deleteUserData($user->entityId());

        $repository->deleteOwnerData($user->entityId());
    }

    protected function deleteShares(User $user): void
    {
        $repository = resolve(ShareRepositoryInterface::class);

        $repository->deleteUserData($user->entityId());

        $repository->deleteOwnerData($user->entityId());
    }
}
