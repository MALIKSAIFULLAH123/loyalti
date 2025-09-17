<?php

namespace MetaFox\Activity\Support;

use Illuminate\Support\Facades\Cache;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class ActivityPinManager implements \MetaFox\Activity\Contracts\ActivityPinManager
{
    private FeedRepositoryInterface $repository;

    public function __construct(FeedRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getCacheName(int $userId): string
    {
        return sprintf(CacheManager::ACTIVITY_PINS_CACHE, $userId);
    }

    public function clearCache(int $userId): void
    {
        $cacheName = $this->getCacheName($userId);
        Cache::forget($cacheName);
    }
}
