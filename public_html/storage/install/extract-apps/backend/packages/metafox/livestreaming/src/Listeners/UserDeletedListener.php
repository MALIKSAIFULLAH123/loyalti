<?php

namespace MetaFox\LiveStreaming\Listeners;

use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function handle(User $user): void
    {
        $repository = resolve(LiveVideoRepositoryInterface::class);
        $repository->deleteUserData($user);

        $repository->deleteOwnerData($user);
    }
}
