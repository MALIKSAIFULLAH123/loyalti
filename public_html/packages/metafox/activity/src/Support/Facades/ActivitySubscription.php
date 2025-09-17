<?php

namespace MetaFox\Activity\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Activity\Models\Subscription as Model;

/**
 * Class ActivitySubscription.
 * @method static Model       getSubscription(int $userId, int $ownerId, ?string $specialType = null)
 * @method static Model       addSubscription(int $userId, int $ownerId, bool $active = true, ?string $specialType = null)
 * @method static bool        deleteSubscription(int $userId, int $ownerId, ?string $specialType = null)
 * @method static bool        isExist(int $userId, int $ownerId)
 * @method static false|Model updateSubscription(int $userId, int $ownerId, bool $active = false, ?string $specialType = null)
 * @mixin \MetaFox\Activity\Support\ActivitySubscription
 */
class ActivitySubscription extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Activity.Subscription';
    }
}
