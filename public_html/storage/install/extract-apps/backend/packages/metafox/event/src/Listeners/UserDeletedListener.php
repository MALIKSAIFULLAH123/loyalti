<?php

namespace MetaFox\Event\Listeners;

use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Event\Repositories\HostInviteRepositoryInterface;
use MetaFox\Event\Repositories\InviteRepositoryInterface;
use MetaFox\Event\Repositories\MemberRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function handle(?User $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        $this->deleteUserData($user);
    }

    protected function deleteUserData(User $model): void
    {
        $this->deleteEvents($model);

        $this->deleteInvites($model);

        $this->deleteHostInvites($model);

        $this->deleteMembers($model);
    }

    protected function deleteMembers(User $model): void
    {
        resolve(MemberRepositoryInterface::class)->deleteUserData($model->entityId());
    }

    protected function deleteHostInvites(User $model): void
    {
        $inviteRepository = resolve(HostInviteRepositoryInterface::class);

        $inviteRepository->deleteInvited($model->entityId());

        $inviteRepository->deleteInviteByUser($model->entityId());
    }

    protected function deleteEvents(User $model): void
    {
        resolve(EventRepositoryInterface::class)->deleteUserData($model->entityId());
    }

    protected function deleteInvites(User $model): void
    {
        $inviteRepository = resolve(InviteRepositoryInterface::class);

        $inviteRepository->deleteInvited($model->entityId());

        $inviteRepository->deleteInvite($model->entityId());
    }
}
