<?php

namespace MetaFox\Activity\Support;

use Illuminate\Support\Facades\Cache;
use MetaFox\Activity\Contracts\ActivityHiddenManager as ActivityHiddenManagerContract;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Hidden as Model;
use MetaFox\Platform\Contracts\User;

class ActivityHiddenManager implements ActivityHiddenManagerContract
{
    /** @var array<int, array<string, mixed>> */
    private $hiddenList = [];

    public function getCacheName(int $userId): string
    {
        return sprintf(CacheManager::ACTIVITY_HIDDEN_CACHE, $userId);
    }

    public function clearCache(int $userId): void
    {
        $cacheName = $this->getCacheName($userId);
        Cache::forget($cacheName);
        if (isset($this->hiddenList[$userId])) {
            unset($this->hiddenList[$userId]);
        }
    }

    public function isHide(User $user, Feed $feed): bool
    {
        $hiddenFeeds = $this->getHiddenFeeds($user);

        return array_key_exists($feed->entityId(), $hiddenFeeds);
    }

    public function getHiddenFeeds(User $user): array
    {
        if (!array_key_exists($user->entityId(), $this->hiddenList)) {
            $this->hiddenList[$user->entityId()] = Cache::remember(
                $this->getCacheName($user->entityId()),
                CacheManager::ACTIVITY_HIDDEN_CACHE_TIME,
                function () use ($user) {
                    return Model::query()
                        ->where('user_id', $user->entityId())
                        ->get(['feed_id'])
                        ->pluck('feed_id', 'feed_id')
                        ->toArray();
                }
            );
        }

        return $this->hiddenList[$user->entityId()];
    }
}
