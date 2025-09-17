<?php

namespace MetaFox\Event\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Event\Contracts\EventMembershipContract;
use MetaFox\Event\Models\Event;
use MetaFox\Platform\Contracts\User;

/**
 * Class EventMembership.
 * @method static int getMembership(Event $event, User $user)
 * @method static array getAllowRoleOptions()
 * @method static array getAllowRsvpOptions()
 * @method static array parseRsvp()
 */
class EventMembership extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EventMembershipContract::class;
    }
}
