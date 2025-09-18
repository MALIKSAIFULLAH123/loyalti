<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Repositories\ActivityRepositoryInterface;
use MetaFox\Group\Repositories\BlockRepositoryInterface;
use MetaFox\Group\Repositories\GroupInviteCodeRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\InviteRepositoryInterface;
use MetaFox\Group\Repositories\MemberRepositoryInterface;
use MetaFox\Group\Repositories\MuteRepositoryInterface;
use MetaFox\Group\Repositories\RequestRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserDeletedListener
{
    public function __construct(
        protected GroupRepositoryInterface           $groupRepository,
        protected MemberRepositoryInterface          $memberRepository,
        protected InviteRepositoryInterface          $inviteRepository,
        protected MuteRepositoryInterface            $muteRepository,
        protected RequestRepositoryInterface         $requestRepository,
        protected BlockRepositoryInterface           $blockRepository,
        protected GroupInviteCodeRepositoryInterface $inviteCodeRepository,
        protected ActivityRepositoryInterface        $activityRepository,
    ) {
    }

    public function handle(?User $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        $this->deleteGroups($user);
        $this->deleteInvite($user);
        $this->deleteMember($user);
        $this->deleteRequest($user);
        $this->deleteUserBlock($user);
        $this->deleteInviteCode($user);
        $this->deleteMute($user);
        $this->deleteActivity($user);
    }

    protected function deleteGroups(User $user): void
    {
        $this->groupRepository->deleteUserData($user);
    }

    protected function deleteMember(User $user): void
    {
        $this->memberRepository->deleteUserData($user);
    }

    protected function deleteInvite(User $user): void
    {
        $this->inviteRepository->deleteUserData($user);

        $this->inviteRepository->deleteOwnerData($user);
    }

    protected function deleteMute(User $user): void
    {
        $this->muteRepository->deleteUserData($user);
    }

    protected function deleteRequest(User $user): void
    {
        $this->requestRepository->deleteUserData($user);
    }

    protected function deleteUserBlock(User $user): void
    {
        $this->blockRepository->deleteUserData($user);
    }

    protected function deleteInviteCode(User $user): void
    {
        $this->inviteCodeRepository->deleteUserData($user);
    }

    protected function deleteActivity(User $user): void
    {
        $this->activityRepository->deleteUserData($user);
    }
}
