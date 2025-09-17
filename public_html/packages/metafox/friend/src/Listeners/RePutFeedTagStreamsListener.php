<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\Contracts\User;

class RePutFeedTagStreamsListener
{
    public function __construct(protected TagFriendRepositoryInterface $repository) { }

    /**
     * @param User|null            $context
     * @param HasTaggedFriend|null $item
     * @param array                $attributes
     * @return void
     */
    public function handle(?User $context, ?HasTaggedFriend $item, array $attributes = []): void
    {
        if (!$item instanceof HasTaggedFriend) {
            return;
        }

        $context        = $context ?? $item?->user;
        $allUsersEntity = $this->repository->getAllTaggedFriends($item);

        $this->repository->putMultipleToTagStream($context, $item, $allUsersEntity, $attributes);
    }
}
