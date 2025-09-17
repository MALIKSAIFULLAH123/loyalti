<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Video\Repositories\VideoRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function handle(User $user): void
    {
        $this->deleteVideos($user);
    }

    protected function deleteVideos(User $user): void
    {
        $repository = resolve(VideoRepositoryInterface::class);
        $repository->deleteUserData($user);

        $repository->deleteOwnerData($user);
    }
}
