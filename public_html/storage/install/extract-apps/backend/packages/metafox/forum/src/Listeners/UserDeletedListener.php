<?php

namespace MetaFox\Forum\Listeners;

use MetaFox\Forum\Models\Moderator;
use MetaFox\Forum\Models\ModeratorAccess;
use MetaFox\Forum\Repositories\ForumPostRepositoryInterface;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function handle(?User $user): void
    {
        if (!$user) {
            return;
        }
        $this->deleteThreads($user);

        $this->deletePosts($user);

        $this->deleteModeration($user);
    }

    protected function deleteModeration(User $user): void
    {
        Moderator::query()
            ->where([
                'user_id' => $user->entityId()
            ])
            ->each(function (Moderator $moderator) {
                $moderator->delete();
            });

        ModeratorAccess::query()
            ->where([
                'user_id' => $user->entityId()
            ])
            ->delete();
    }

    protected function deleteThreads(User $user)
    {
        $threadService = resolve(ForumThreadRepositoryInterface::class);
        $threadService->deleteUserData($user);
        $threadService->deleteOwnerData($user);
    }

    protected function deletePosts(User $user)
    {
        $postService = resolve(ForumPostRepositoryInterface::class);
        $postService->deleteUserData($user);
        $postService->deleteOwnerData($user);
    }
}
