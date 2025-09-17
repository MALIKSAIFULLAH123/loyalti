<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Repositories\FeedRepositoryInterface;

/**
 * Class PushFeedOnTopListener.
 * @ignore
 */
class PushFeedOnTopListener
{
    /**
     * @param int $feedId
     *
     * @return bool
     */
    public function handle(int $feedId): bool
    {
        $service = resolve(FeedRepositoryInterface::class);

        return $service->pushFeedOnTop($feedId);
    }
}
