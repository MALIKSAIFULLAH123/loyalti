<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * Class GetFeedByItemIdListener.
 * @ignore
 */
class GetFeedByItemIdListener
{
    public function __construct(protected FeedRepositoryInterface $repository)
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function handle(
        ?User $context,
        int $itemId,
        string $itemType,
        string $typeId,
        bool $checkPermission = true
    ): ?Feed {
        if (!$context) {
            return null;
        }

        return $this->repository->getFeedByItemId(
            $context,
            $itemId,
            $itemType,
            $typeId,
            $checkPermission
        );
    }
}
