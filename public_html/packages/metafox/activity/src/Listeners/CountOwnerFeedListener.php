<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;

class CountOwnerFeedListener
{
    public function handle(string $ownerType, int $ownerId, ?string $status = MetaFoxConstant::ITEM_STATUS_APPROVED, ?int $userId = null): int
    {
        return resolve(FeedRepositoryInterface::class)->countFeed($ownerType, $ownerId, $status, $userId);
    }
}
