<?php

namespace MetaFox\Event\Observers;

use Illuminate\Support\Collection;
use MetaFox\Event\Models\Event as Model;
use MetaFox\Event\Models\HostInvite;
use MetaFox\Event\Models\Invite;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Repositories\HostInviteRepositoryInterface;
use MetaFox\Event\Repositories\InviteRepositoryInterface;

/**
 * Class EventObserver.
 */
class EventObserver
{
    public function deleting(Model $model): void
    {
        $model->loadMissing(['members']);

        $members = $model->members;
        if ($members instanceof Collection) {
            $members->each(function (Member $member) {
                $member->delete();
            });
        }
    }

    /**
     * Invoked when a model is deleted.
     *
     * @param Model $model
     */
    public function deleted(Model $model): void
    {
        $model->loadMissing(['invites', 'hostInvites']);

        $invites = $model->invites;

        if ($invites instanceof Collection) {
            $invites->each(function (Invite $invite) {
                $invite->delete();

                resolve(InviteRepositoryInterface::class)->deleteNotification($invite);
            });
        }

        $hostInvites = $model->hostInvites;

        if ($hostInvites instanceof Collection) {
            $hostInvites->each(function (HostInvite $hostInvite) {
                $hostInvite->delete();

                resolve(HostInviteRepositoryInterface::class)->massDeleteNotification($hostInvite);
            });
        }

        $model->categories()->sync([]);
    }
}

// end stub
