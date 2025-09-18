<?php

namespace MetaFox\Group\Observers;

use MetaFox\Group\Models\Member as Member;
use MetaFox\Group\Repositories\InviteRepositoryInterface;
use MetaFox\Group\Repositories\RequestRepositoryInterface;
use MetaFox\Platform\Contracts\HasTotalMember;
use MetaFox\Platform\Contracts\User;

/**
 * Class MemberObserver.
 * @ignore
 */
class MemberObserver
{
    /**
     * @param Member $model
     */
    public function created(Member $model): void
    {
        $group = $model->group;
        if ($group instanceof HasTotalMember) {
            $group->incrementAmount('total_member');
        }

        $user = $model->user;
        if ($user instanceof User) {
            resolve(InviteRepositoryInterface::class)
                ->handelInviteJoinGroup($group->entityId(), $user);
        }
    }

    /**
     * @param Member $model
     */
    public function deleted(Member $model): void
    {
        $group = $model->group;
        if ($group instanceof HasTotalMember) {
            $group->decrementAmount('total_member');
        }
    }
}
