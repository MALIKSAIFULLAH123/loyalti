<?php

namespace MetaFox\Event\Listeners;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Event\Repositories\HostInviteRepositoryInterface;
use MetaFox\Event\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\UserEntity;

class FriendInvitedBuilderListener
{
    public function __construct(
        protected EventRepositoryInterface      $eventRepository,
        protected InviteRepositoryInterface     $inviteRepository,
        protected HostInviteRepositoryInterface $hostInviteRepository
    ) {
    }

    public function handle(?User $context, string $itemType, int $itemId): ?Builder
    {
        if ($itemType != Event::ENTITY_TYPE) {
            return null;
        }

        $event                = $this->eventRepository->find($itemId);
        $table                = $this->inviteRepository->getModel()->getTable();
        $memberPendingInvites = $this->inviteRepository->getBuilderPendingInvites($event)->select("$table.owner_id");

        $hostInviteTable    = $this->hostInviteRepository->getModel()->getTable();
        $hostPendingInvites = $this->hostInviteRepository->getBuilderPendingInvites($event)->select("$hostInviteTable.owner_id");

        return UserEntity::query()->select('id')
            ->whereIn('id', $memberPendingInvites)
            ->orWhereIn('id', $hostPendingInvites);
    }
}
