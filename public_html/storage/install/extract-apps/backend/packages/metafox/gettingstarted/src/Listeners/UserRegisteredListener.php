<?php

namespace MetaFox\GettingStarted\Listeners;

use MetaFox\GettingStarted\Repositories\UserFirstLoginRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Models\User;

class UserRegisteredListener
{
    public function __construct(protected UserFirstLoginRepositoryInterface $userFirstLoginRepository)
    {
    }

    public function handle(?User $user)
    {
        if (!$user instanceof User) {
            return;
        }

        $this->userFirstLoginRepository->initUserFirstLoginData($user);
    }
}
