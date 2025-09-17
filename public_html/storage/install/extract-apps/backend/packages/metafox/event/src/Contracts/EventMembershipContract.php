<?php

namespace MetaFox\Event\Contracts;

use MetaFox\Event\Models\Event;
use MetaFox\Platform\Contracts\User;

interface EventMembershipContract
{
    public function getMembership(Event $event, User $user): int;

    /**
     * getAllowRoleOptions.
     *
     * @return array<mixed>
     */
    public function getAllowRoleOptions(): array;

    /**
     * getAllowRsvpOptions.
     *
     * @return array<mixed>
     */
    public function getAllowRsvpOptions(): array;

    /**
     * @return array
     */
    public function parseRsvp(): array;
}
