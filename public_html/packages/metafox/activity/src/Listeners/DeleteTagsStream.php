<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Activity\Support\Facades\ActivityFeed;
use MetaFox\Platform\Contracts\User;

class DeleteTagsStream
{
    public function __construct(protected FeedRepositoryInterface $repository)
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function handle(?User $context, int $friendId, int $itemId, string $itemType, string $typeId): void
    {
        if (!$context) {
            return;
        }

        $feed           = $this->repository->getFeedByItemId($context, $itemId, $itemType, $typeId, false);
        $conditions     = [
            'feed_id'  => $feed->entityId(),
            'user_id'  => $feed->userId(),
            'owner_id' => $friendId,
        ];

        if (!$feed instanceof Feed) {
            return;
        }

        if ($feed->userId() != $feed->ownerId()) {
            return;
        }

        ActivityFeed::deleteTagsStream($conditions);
    }
}
