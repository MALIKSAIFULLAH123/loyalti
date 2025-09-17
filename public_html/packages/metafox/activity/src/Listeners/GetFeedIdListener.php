<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * Class GetFeedIdListener.
 * @ignore
 */
class GetFeedIdListener
{
    /**
     * @param User|null $user
     * @param int       $feedId
     *
     * @return Content
     * @throws AuthorizationException
     */
    public function handle(?User $user, int $feedId): Content
    {
        $service = resolve(FeedRepositoryInterface::class);

        return $service->getFeed($user, $feedId);
    }
}
