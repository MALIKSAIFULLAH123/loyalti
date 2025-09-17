<?php

namespace MetaFox\Activity\Contracts;

use MetaFox\Activity\Models\Snooze as Model;
use MetaFox\Activity\Support\Constants;
use MetaFox\Platform\Contracts\User;

/**
 * Interface SnoozeContract.
 */
interface SnoozeContract
{
    /**
     * @return array
     */
    public function getAllowedSnoozeTypes(): array;

    /**
     * @return array
     */
    public function getSnoozeOptions(): array;

    /**
     * @return string
     */
    public function getSearchSnoozeDesc(): string;

    /**
     * @param  int  $userId
     * @return void
     */
    public function clearCache(int $userId): void;

    /**
     * @param  User      $user
     * @param  User|null $owner
     * @return bool
     */
    public function isSnooze(User $user, ?User $owner): bool;

    /**
     * @param  User      $user
     * @param  User|null $owner
     * @return bool
     */
    public function isSnoozeForever(User $user, ?User $owner): bool;

    /**
     * @param  int   $userId
     * @return array
     */
    public function fetchSnoozedUsers(int $userId): array;

    /**
     * @param  User  $user
     * @param  User  $owner
     * @param  int   $snoozeDay
     * @return Model
     */
    public function snooze(User $user, User $owner, int $snoozeDay = Constants::DEFAULT_SNOOZE_DAYS): Model;

    /**
     * @param  User  $user
     * @param  User  $owner
     * @return Model
     */
    public function snoozeForever(User $user, User $owner): Model;

    /**
     * @param  User  $user
     * @param  User  $owner
     * @return Model
     */
    public function unSnooze(User $user, User $owner): Model;
}
