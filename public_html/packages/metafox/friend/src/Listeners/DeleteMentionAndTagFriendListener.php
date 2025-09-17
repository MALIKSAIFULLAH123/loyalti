<?php

namespace MetaFox\Friend\Listeners;

use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

/**
 * Class DeleteMentionAndTagFriendListener.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class DeleteMentionAndTagFriendListener
{
    public function __construct(protected TagFriendRepositoryInterface $tagFriendRepository) { }

    /**
     * @param Entity    $item
     * @param User|null $friend
     *
     * @return void
     */
    public function handle(Entity $item, ?User $friend): void
    {
        $this->tagFriendRepository->deleteMentionAndTagFriend($item, $friend);
    }
}
