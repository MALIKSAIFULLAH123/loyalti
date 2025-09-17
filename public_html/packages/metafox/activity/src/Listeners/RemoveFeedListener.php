<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;

/**
 * Class DeleteFeedListener.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @ignore
 */
class RemoveFeedListener
{
    /**
     * @param  Feed|null  $feed
     *
     * @return void
     * @throws AuthenticationException
     */
    public function handle(?Feed $feed): void
    {
        if (null == $feed) {
            return;
        }
        $context = user();

        $this->feedRepository()->archiveFeed($context, $feed->entityId());
    }

    private function feedRepository(): FeedRepositoryInterface
    {
        return resolve(FeedRepositoryInterface::class);
    }
}
