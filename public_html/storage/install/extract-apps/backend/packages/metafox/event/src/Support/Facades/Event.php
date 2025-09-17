<?php

namespace MetaFox\Event\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Event\Contracts\EventContract;
use MetaFox\Event\Models\Event as EventModel;
use MetaFox\Platform\Contracts\User;

/**
 * @see \MetaFox\Event\Support\Event
 * @method static bool       checkFeedReactingPermission(User $user, User $owner)
 * @method static bool       checkPermissionMassEmail(User $user, int $eventId)
 * @method static array|null createLocationWithName(string $locationName)
 * @method static array getStatusTexts(EventModel $event)
 */
class Event extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EventContract::class;
    }
}
