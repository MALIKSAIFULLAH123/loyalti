<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Repositories\InviteRepositoryInterface;
use MetaFox\Group\Repositories\RequestRepositoryInterface;
use MetaFox\User\Models\UserEntity;

class GetInviteBuilderListener
{
    public function __construct(protected InviteRepositoryInterface $inviteRepository, protected RequestRepositoryInterface $requestRepository)
    {
    }

    /**
     * @param mixed $owner
     *
     * @return array|null
     */
    public function handle($owner): ?Builder
    {
        if (!$owner instanceof Group) {
            return null;
        }

        $tableInvite  = $this->inviteRepository->getModel()->getTable();
        $tableRequest = $this->requestRepository->getModel()->getTable();

        $requestBuilder = $this->requestRepository->getBuilderPendingRequests($owner)->select("$tableRequest.user_id");
        $inviteBuilder  = $this->inviteRepository->getBuilderPendingInvites($owner)->select("$tableInvite.owner_id");

        return UserEntity::query()->select('id')
            ->whereIn('id', $requestBuilder)
            ->orWhereIn('id', $inviteBuilder);
    }
}
