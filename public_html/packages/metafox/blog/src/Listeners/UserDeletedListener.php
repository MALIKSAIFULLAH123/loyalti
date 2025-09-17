<?php

namespace MetaFox\Blog\Listeners;

use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function handle(?User $user): void
    {
        if (!$user) {
            return;
        }
        $this->deleteBlogs($user);
    }

    protected function deleteBlogs(User $user): void
    {
        $repository = resolve(BlogRepositoryInterface::class);

        $repository->deleteUserData($user);

        $repository->deleteOwnerData($user);
    }
}
