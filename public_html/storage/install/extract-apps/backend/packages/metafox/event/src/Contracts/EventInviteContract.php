<?php

namespace MetaFox\Event\Contracts;

use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\HostInvite;
use MetaFox\Event\Models\Invite;
use MetaFox\Event\Models\InviteCode;
use MetaFox\Platform\Contracts\User;

interface EventInviteContract
{
    public function getPendingInvite(Event $event, User $user): ?Invite;

    public function getPendingHostInvite(Event $event, User $user): ?HostInvite;

    public function getAvailableInvite(Event $event, User $user, ?string $inviteCode = null): Invite|InviteCode|null;

    public function hasPendingHostInvite(Event $event, User $user): bool;
}
