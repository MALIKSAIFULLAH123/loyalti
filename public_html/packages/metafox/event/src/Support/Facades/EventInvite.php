<?php

namespace MetaFox\Event\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Event\Contracts\EventInviteContract;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\HostInvite;
use MetaFox\Event\Models\Invite;
use MetaFox\Event\Models\InviteCode;
use MetaFox\Platform\Contracts\User;

/**
 * Class EventMembership.
 * @method static Invite|null            getPendingInvite(Event $event, User $user)
 * @method static Invite|InviteCode|null getAvailableInvite(Event $event, User $user, ?string $inviteCode = null)
 * @method static bool                   hasPendingHostInvite(Event $event, User $user)
 * @method static HostInvite|null        getPendingHostInvite(Event $event, User $user)
 */
class EventInvite extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EventInviteContract::class;
    }
}
