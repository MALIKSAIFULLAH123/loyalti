<?php

namespace MetaFox\Announcement\Listeners;

use MetaFox\Announcement\Repositories\AnnouncementViewRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function handle(?User $user): void
    {
        if (!$user) {
            return;
        }

        $this->deleteAnnouncementView($user);
    }

    protected function deleteAnnouncementView(User $user): void
    {
        $repository = resolve(AnnouncementViewRepositoryInterface::class);

        $repository->deleteUserData($user);
    }
}
