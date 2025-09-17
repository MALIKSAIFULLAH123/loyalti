<?php

namespace MetaFox\Activity\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\Activity\Models\Snooze;
use MetaFox\Activity\Support\Constants;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Interface SnoozeRepositoryInterface.
 * @mixin AbstractRepository
 * @method Snooze find($id, $columns = ['*'])
 * @method Snooze getModel()
 * @method Snooze create($params = [])
 */
interface SnoozeRepositoryInterface
{
    /**
     * If snoozes does not have subscriptions, bulk delete.
     * @return void
     */
    public function deleteExpiredSnoozesNotHavingSubscription(): void;

    /**
     * @return void
     */
    public function deleteExpiredSnoozesHavingSubscription(): void;

    /**
     * @param  User      $context
     * @param  array     $params
     * @return Paginator
     */
    public function getSnoozes(User $context, array $params): Paginator;

    /**
     * @param  User   $user
     * @param  User   $owner
     * @param  int    $snoozeDay
     * @return Snooze
     */
    public function snooze(User $user, User $owner, int $snoozeDay = Constants::DEFAULT_SNOOZE_DAYS): Snooze;

    /**
     * @param  User   $user
     * @param  User   $owner
     * @return Snooze
     */
    public function snoozeForever(User $user, User $owner): Snooze;

    /**
     * @param  User   $user
     * @param  User   $owner
     * @return Snooze
     */
    public function unSnooze(User $user, User $owner): Snooze;
}
