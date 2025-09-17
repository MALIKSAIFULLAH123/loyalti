<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * Class CountFeedPendingOnOwnerListener.
 * @ignore
 */
class CountFeedPendingOnOwnerListener
{
    /**
     * @param User|null $context
     * @param User      $owner
     *
     * @return int
     */
    public function handle(?User $context, User $owner): int
    {
        $service = resolve(FeedRepositoryInterface::class);

        return $service->countFeedPendingOnOwner($context, $owner);
    }
}
