<?php

namespace MetaFox\GettingStarted\Listeners;

use MetaFox\GettingStarted\Repositories\UserFirstLoginRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function __construct(protected UserFirstLoginRepositoryInterface $userFirstLoginRepository)
    {
    }

    public function handle(?User $user): void
    {
        if (!$user) {
            return;
        }

        $this->userFirstLoginRepository->deleteByUser($user);
    }
}
