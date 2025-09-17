<?php

namespace MetaFox\Group\Observers;

use MetaFox\Group\Models\GroupInviteCode;
use MetaFox\Group\Models\Invite;
use MetaFox\Group\Repositories\InviteRepositoryInterface;

/**
 * Class GroupInviteCodeObserver.
 * @ignore
 */
class GroupInviteCodeObserver
{
    public function updated(GroupInviteCode $invite): void
    {
        $service = resolve(InviteRepositoryInterface::class);
        if ($invite->status != GroupInviteCode::STATUS_ACTIVE) {
            $service->getModel()->newQuery()
                ->where('code', $invite->code)
                ->where('status_id', Invite::STATUS_PENDING)
                ->update(['status_id' => Invite::STATUS_CANCELLED]);
        }
    }
}

// end stub
