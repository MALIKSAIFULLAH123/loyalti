<?php

namespace MetaFox\Like\Listeners;

use Illuminate\Support\Facades\Cache;
use MetaFox\Like\Http\Resources\v1\Reaction\ReactionDetail;
use MetaFox\Like\Repositories\LikeRepositoryInterface;
use MetaFox\Like\Support\CacheManager;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\User;

/**
 * Class UserReactedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class UserReactedListener
{
    /** @var LikeRepositoryInterface */
    private $repository;

    public function __construct(LikeRepositoryInterface $likeRepository)
    {
        $this->repository = $likeRepository;
    }

    /**
     * @param User         $context
     * @param HasTotalLike $content
     *
     * @return array<mixed>|ReactionDetail
     */
    public function handle(?User $context, HasTotalLike $content)
    {
        $key  = sprintf(CacheManager::USER_REACTED_CACHE, $content->entityId(), $content->entityType(), $context->entityId());

        $time = CacheManager::USER_REACTED_CACHE_TIME;

        return Cache::remember($key, $time, function () use ($context, $content) {
            $like = $this->repository->getLike($context, $content);

            if ($like === null) {
                return [];
            }

            $reaction = $like->reaction;

            return new ReactionDetail($reaction);
        });
    }
}
