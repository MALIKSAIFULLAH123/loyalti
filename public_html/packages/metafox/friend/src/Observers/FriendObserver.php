<?php

namespace MetaFox\Friend\Observers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use MetaFox\Friend\Models\Friend;
use MetaFox\Friend\Repositories\FriendListRepositoryInterface;
use MetaFox\Friend\Support\CacheManager;
use MetaFox\Platform\Contracts\User;

/**
 * Class FriendObserver.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class FriendObserver
{
    /**
     * @param  Friend $model
     * @return void
     */
    public function created(Friend $model): void
    {
        $this->clearCache($model);
        $this->increaseUserActivityPoint($model);
        $owner = $model->owner;
        if ($owner instanceof \MetaFox\User\Models\User) {
            $owner->incrementAmount('total_friend');
        }
    }

    /**
     * @param  Friend $model
     * @return void
     */
    public function updated(Friend $model): void
    {
        $this->clearCache($model);
    }

    /**
     * @throws AuthorizationException
     */
    public function deleted(Friend $model): void
    {
        $this->updateFriendList($model);

        // Delete friend feed.
        $model->activity_feed?->delete();

        $this->clearCache($model);

        $this->decreaseUserActivityPoint($model);

        $owner = $model->owner;

        if ($owner instanceof \MetaFox\User\Models\User) {
            $owner->decrementAmount('total_friend');
        }
    }

    /**
     * @param  Friend $model
     * @return void
     */
    private function clearCache(Friend $model): void
    {
        Cache::forget(sprintf(CacheManager::IS_FRIEND_CACHE, $model->userId(), $model->ownerId()));
        Cache::forget(sprintf(CacheManager::FRIEND_LIST_IDS, $model->userId()));
    }

    private function increaseUserActivityPoint(Friend $model): void
    {
        $user = $model->user;
        if (!$user instanceof User) {
            return;
        }

        app('events')->dispatch('activitypoint.increase_user_point', [$user, $model, 'added_new_friend']);
    }

    private function decreaseUserActivityPoint(Friend $model): void
    {
        $user = $model->user;
        if (!$user instanceof User) {
            return;
        }

        app('events')->dispatch('activitypoint.decrease_user_point', [$user, $model, 'added_new_friend']);
    }

    protected function updateFriendList(Friend $model): void
    {
        //Remove friend in list
        $user = $model->user;
        if (!$user instanceof User) {
            return;
        }

        $friendListRepository = resolve(FriendListRepositoryInterface::class);
        $friendLists          = $friendListRepository->getFriendListByUser($model->userId())->all();
        $friendListRepository->removeFriendsFromFriendLists($friendLists, [$model->ownerId()]);
    }
}
