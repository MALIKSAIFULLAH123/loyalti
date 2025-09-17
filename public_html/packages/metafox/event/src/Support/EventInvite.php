<?php

namespace MetaFox\Event\Support;

use MetaFox\Event\Contracts\EventInviteContract;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\HostInvite;
use MetaFox\Event\Models\Invite;
use MetaFox\Event\Models\InviteCode;
use MetaFox\Event\Repositories\HostInviteRepositoryInterface;
use MetaFox\Event\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Event\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class EventInvite implements EventInviteContract
{
    /**
     * @return InviteRepositoryInterface
     */
    private function inviteRepository(): InviteRepositoryInterface
    {
        return resolve(InviteRepositoryInterface::class);
    }

    /**
     * @return HostInviteRepositoryInterface
     */
    private function hostInviteRepository(): HostInviteRepositoryInterface
    {
        return resolve(HostInviteRepositoryInterface::class);
    }

    /**
     * @return InviteCodeRepositoryInterface
     */
    private function inviteCodeRepository(): InviteCodeRepositoryInterface
    {
        return resolve(InviteCodeRepositoryInterface::class);
    }

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return Invite|null
     */
    public function getPendingInvite(Event $event, User $user): ?Invite
    {
        return $this->inviteRepository()->getPendingInvite($event->entityId(), $user);
    }

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return HostInvite|null
     */
    public function getPendingHostInvite(Event $event, User $user): ?HostInvite
    {
        return $this->hostInviteRepository()->getPendingInvite($event->entityId(), $user);
    }

    public function getAvailableInvite(Event $event, User $user, ?string $inviteCode = null): Invite|InviteCode|null
    {
        if ($inviteCode == null) {
            return $this->getPendingInvite($event, $user);
        }

        if (!$event->isMember($user)) {
            return $this->inviteCodeRepository()->verifyCodeByValueAndContext($user, $event, $inviteCode);
        }

        return null;
    }

    public function hasPendingHostInvite(Event $event, User $user): bool
    {
        $pendingInvite = $this->getPendingHostInvite($event, $user);

        return $pendingInvite instanceof HostInvite;
    }
}
