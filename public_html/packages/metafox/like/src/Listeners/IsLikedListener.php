<?php

namespace MetaFox\Like\Listeners;

use Illuminate\Support\Facades\Cache;
use MetaFox\Like\Repositories\LikeRepositoryInterface;
use MetaFox\Like\Support\CacheManager;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\User;

/**
 * Class IsLikedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class IsLikedListener
{
    /** @var LikeRepositoryInterface */
    private $repository;

    /**
     * @param LikeRepositoryInterface $likeRepository
     */
    public function __construct(LikeRepositoryInterface $likeRepository)
    {
        $this->repository = $likeRepository;
    }

    /**
     * @param  User|null    $context
     * @param  HasTotalLike $content
     * @return bool
     */
    public function handle(?User $context, HasTotalLike $content): bool
    {
        if (!$context) {
            return false;
        }

        $key = sprintf(
            CacheManager::IS_LIKED_CACHE,
            $content->entityId(),
            $content->entityType(),
            $context->entityId()
        );

        return Cache::remember($key, CacheManager::IS_LIKED_CACHE_TIME, function () use ($context, $content) {
            return $this->repository->isLiked($context, $content);
        });
    }
}
