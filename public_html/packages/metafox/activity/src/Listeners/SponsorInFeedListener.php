<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * Class SponsorInFeedListener.
 * @ignore
 */
class SponsorInFeedListener
{
    /**
     * @param User|null $context
     * @param Content   $content
     * @param int|null  $newValue
     *
     * @return bool
     * @throws AuthorizationException
     */
    public function handle(?User $context, Content $content, ?int $newValue = null): bool
    {
        $service = resolve(FeedRepositoryInterface::class);
        $feed    = $service->getFeedByItem($context, $content);

        if (!$feed instanceof Feed) {
            throw (new ModelNotFoundException())->setModel(Feed::ENTITY_TYPE);
        }

        return $service->sponsorFeed($context, $feed, $newValue);
    }
}
