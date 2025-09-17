<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Repositories\FriendListRepositoryInterface;
use MetaFox\Friend\Repositories\FriendRepositoryInterface;
use MetaFox\Friend\Repositories\FriendRequestRepositoryInterface;
use MetaFox\Friend\Repositories\FriendTagBlockedRepositoryInterface;
use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function handle(?User $user): void
    {
        if (!$user) {
            return;
        }
        $this->deleteFriends($user);

        $this->deleteFriendLists($user);

        $this->deleteFriendRequests($user);

        $this->deleteFriendTags($user);

        $this->deleteBlockedFriendTags($user);

        $this->deleteIgnoredSuggestions($user);

        $this->deleteFriendListsData($user);
    }

    protected function deleteIgnoredSuggestions(User $user): void
    {
        /** @var FriendRepositoryInterface $repository */
        $repository = resolve(FriendRepositoryInterface::class);
        $repository->deleteUserSuggestionIgnoreData($user->entityId());
    }

    protected function deleteBlockedFriendTags(User $user): void
    {
        /** @var FriendTagBlockedRepositoryInterface $repository */
        $repository = resolve(FriendTagBlockedRepositoryInterface::class);

        $repository->deleteUserData($user);
    }

    protected function deleteFriendTags(User $user): void
    {
        /** @var TagFriendRepositoryInterface $repository */
        $repository = resolve(TagFriendRepositoryInterface::class);

        $repository->deleteUserData($user);
        $repository->deleteOwnerData($user);
    }

    protected function deleteFriendRequests(User $user): void
    {
        /** @var FriendRequestRepositoryInterface $repository */
        $repository = resolve(FriendRequestRepositoryInterface::class);

        $repository->deleteUserData($user);

        $repository->deleteOwnerData($user);
    }

    protected function deleteFriendLists(User $user): void
    {
        /** @var FriendListRepositoryInterface $repository */
        $repository = resolve(FriendListRepositoryInterface::class);

        $repository->deleteUserData($user);
    }

    protected function deleteFriendListsData(User $user): void
    {
        /** @var FriendListRepositoryInterface $repository */
        $repository = resolve(FriendListRepositoryInterface::class);

        $repository->deleteUserForListData($user);
    }

    protected function deleteFriends(User $user): void
    {
        /** @var FriendRepositoryInterface $repository */
        $repository = resolve(FriendRepositoryInterface::class);
        $repository->deleteUserData($user);
        $repository->deleteOwnerData($user);
    }
}
