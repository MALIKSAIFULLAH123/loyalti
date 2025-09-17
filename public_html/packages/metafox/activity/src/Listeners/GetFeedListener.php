<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * Class GetFeedListener.
 * @ignore
 */
class GetFeedListener
{
    /**
     * @param User|null   $context
     * @param Content     $content
     * @param string|null $typeId
     *
     * @return Feed
     * @throws AuthorizationException
     */
    public function handle(?User $context, Content $content, ?string $typeId = null): Feed
    {
        $service = resolve(FeedRepositoryInterface::class);

        return $service->getFeedByItem($context, $content, $typeId);
    }
}
