<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Core\Repositories\LinkRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function handle(?User $user): void
    {
        if (!$user) {
            return;
        }

        $this->deleteLinks($user);
    }

    protected function deleteLinks(?User $user): void
    {
        if (!$user) {
            return;
        }
        $repository = resolve(LinkRepositoryInterface::class);

        $repository->deleteUserData($user->entityId());

        $repository->deleteOwnerData($user->entityId());
    }
}
