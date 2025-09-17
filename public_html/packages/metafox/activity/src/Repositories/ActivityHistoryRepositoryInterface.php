<?php

namespace MetaFox\Activity\Repositories;

use Illuminate\Support\Collection;
use MetaFox\Activity\Models\ActivityHistory;
use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface ActivityHistory
 *
 * @mixin BaseRepository
 * stub: /packages/repositories/interface.stub
 */
interface ActivityHistoryRepositoryInterface
{
    /**
     * @param  User  $user
     * @param  Feed  $feed
     * @return ActivityHistory
     */
    public function createHistory(User $user, Feed $feed): ActivityHistory;

    /**
     * @param  User  $user
     * @param  int   $id
     * @return Collection
     */
    public function viewHistories(User $user, int $id): Collection;

    /**
     * @param  int  $id
     * @return bool
     */
    public function checkExists(int $id): bool;

    /**
     * @param  ActivityHistory  $activityHistory
     * @param  array            $attributes
     * @return void
     */
    public function updateHistory(ActivityHistory $activityHistory, array $attributes): void;
}
