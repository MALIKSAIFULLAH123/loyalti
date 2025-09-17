<?php

namespace MetaFox\Story\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Story\Repositories\StoryReactionRepositoryInterface;
use MetaFox\Story\Repositories\StoryRepositoryInterface;
use MetaFox\Story\Repositories\StorySetRepositoryInterface;
use MetaFox\Story\Repositories\StoryViewRepositoryInterface;

class UserDeletedListener
{
    public function handle(User $user): void
    {
        $this->deleteStories($user);
        $this->deleteViewStories($user);
        $this->deleteReactionStories($user);
        $this->deleteStorySet($user);
    }

    protected function deleteStories(User $user): void
    {
        /**@var StoryRepositoryInterface $repository */
        $repository = resolve(StoryRepositoryInterface::class);

        $repository->deleteUserData($user);

        $repository->deleteOwnerData($user);
    }

    protected function deleteViewStories(User $user): void
    {
        /**@var StoryViewRepositoryInterface $repository */
        $repository = resolve(StoryViewRepositoryInterface::class);

        $repository->deleteUserData($user);
    }

    protected function deleteReactionStories(User $user): void
    {
        /**@var StoryReactionRepositoryInterface $repository */
        $repository = resolve(StoryReactionRepositoryInterface::class);

        $repository->deleteUserData($user);
    }

    protected function deleteStorySet(User $user): void
    {
        /**@var StorySetRepositoryInterface $repository */
        $repository = resolve(StorySetRepositoryInterface::class);

        $repository->deleteUserData($user);
    }
}
