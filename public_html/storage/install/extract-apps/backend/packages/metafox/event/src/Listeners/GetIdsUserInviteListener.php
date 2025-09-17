<?php

namespace MetaFox\Event\Listeners;

use MetaFox\Event\Models\Event;
use MetaFox\Event\Repositories\HostInviteRepositoryInterface;
use MetaFox\Event\Repositories\InviteRepositoryInterface;

/**
 * @deprecated Remove in v5.2
 */
class GetIdsUserInviteListener
{
    public function __construct(protected InviteRepositoryInterface $inviteRepository, protected HostInviteRepositoryInterface $hostInviteRepository)
    {
    }

    /**
     * @param mixed $owner
     *
     * @return array|null
     */
    public function handle($owner): ?array
    {
        if (!$owner instanceof Event) {
            return null;
        }

        $memberPendingInvites = $this->inviteRepository->getPendingInvites($owner)
            ->pluck('owner_id')
            ->toArray();

        $hostPendingInvites = $this->hostInviteRepository->getPendingInvites($owner)
            ->pluck('owner_id')
            ->toArray();

        return array_unique(array_merge($memberPendingInvites, $hostPendingInvites));
    }
}
