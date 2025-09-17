<?php

namespace MetaFox\Event\Listeners;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Repositories\HostInviteRepositoryInterface;
use MetaFox\Event\Repositories\InviteRepositoryInterface;
use MetaFox\User\Models\UserEntity;

class GetInviteBuilderListener
{
    public function __construct(protected InviteRepositoryInterface $inviteRepository, protected HostInviteRepositoryInterface $hostInviteRepository)
    {
    }

    /**
     * @param mixed $owner
     *
     * @return array|null
     */
    public function handle($owner): ?Builder
    {
        if (!$owner instanceof Event) {
            return null;
        }

        $table                = $this->inviteRepository->getModel()->getTable();
        $memberPendingInvites = $this->inviteRepository->getBuilderPendingInvites($owner)->select("$table.owner_id");

        $hostInviteTable    = $this->hostInviteRepository->getModel()->getTable();
        $hostPendingInvites = $this->hostInviteRepository->getBuilderPendingInvites($owner)->select("$hostInviteTable.owner_id");

        return UserEntity::query()->select('id')
            ->whereIn('id', $memberPendingInvites)
            ->orWhereIn('id', $hostPendingInvites);
    }
}
