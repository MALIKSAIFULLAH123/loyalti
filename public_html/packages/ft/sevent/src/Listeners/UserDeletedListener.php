<?php

namespace Foxexpert\Sevent\Listeners;

use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function handle(?User $user): void
    {
        if (!$user) {
            return;
        }
        $this->deleteSevents($user);
    }

    protected function deleteSevents(User $user): void
    {
        $repository = resolve(SeventRepositoryInterface::class);

        $repository->deleteUserData($user);

        $repository->deleteOwnerData($user);
    }
}
