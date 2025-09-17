<?php

namespace MetaFox\Activity\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Activity\Contracts\SnoozeContract;
use MetaFox\Activity\Models\Snooze as Model;
use MetaFox\Activity\Support\Constants;
use MetaFox\Platform\Contracts\User;

/**
 * Class Snooze.
 * @method static array  getAllowedSnoozeTypes()
 * @method static array  getSnoozeOptions()
 * @method static string getSearchSnoozeDesc()
 * @method static Model  snooze(User $user, User $owner, int $snoozeDay = Constants::DEFAULT_SNOOZE_DAYS)
 * @method static Model  snoozeForever(User $user, User $owner)
 * @method static Model  unSnooze(User $user, User $owner)
 * @method static bool   isSnooze(User $user, ?User $owner)
 * @method static bool   isSnoozeForever(User $user, ?User $owner)
 * @method static array  fetchSnoozedUsers(int $userId)
 * @method static void   clearCache(int $userId)
 * @mixin \MetaFox\Activity\Support\Snooze
 */
class Snooze extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SnoozeContract::class;
    }
}
