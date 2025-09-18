<?php

namespace MetaFox\Group\Observers;

use MetaFox\Group\Models\Invite;
use MetaFox\Group\Repositories\InviteRepositoryInterface;
use MetaFox\Group\Support\InviteType;

/**
 * Class InviteObserver.
 * @ignore
 */
class InviteObserver
{
    public function __construct(protected InviteRepositoryInterface $repository)
    {
    }

    public function creating(Invite $invite): void
    {
    }
    public function created(Invite $invite): void
    {
        if ($invite->invite_type == InviteType::INVITED_MEMBER) {
            $invite->group->incrementAmount('total_invite');
        }
    }

    public function updating(Invite $invite): void
    {
        $expired = $this->repository->handleExpiredInvite($invite->getInviteType(), $invite->expired_at);

        if ($invite->status_id == Invite::STATUS_PENDING) {
            $invite->expired_at = $expired;
        }
    }
    public function deleted(Invite $invite): void
    {
        $invite->group->decrementAmount('total_invite');
    }
}

// end stub
