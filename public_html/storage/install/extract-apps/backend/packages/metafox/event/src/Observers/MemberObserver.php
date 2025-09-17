<?php

namespace MetaFox\Event\Observers;

use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Contracts\HasTotalInterested;
use MetaFox\Platform\Contracts\HasTotalMember;
use MetaFox\Platform\Contracts\User;

/**
 * Class MemberObserver.
 */
class MemberObserver
{
    private function inviteRepository(): InviteRepositoryInterface
    {
        return resolve(InviteRepositoryInterface::class);
    }

    /**
     * @param Member $model
     */
    public function created(Member $model): void
    {
        $event = $model->event;
        $user  = $model->user;

        $this->increaseAmounts($event, $model->rsvp_id);

        if ($user instanceof User && $model->hasMemberPrivileges()) {
            $this->inviteRepository()->handleJoinEvent($event->entityId(), $user);
        }
    }

    /**
     * @param Member $model
     */
    public function deleted(Member $model): void
    {
        $event = $model->event;
        if (!$event instanceof Event) {
            return;
        }

        $this->decreaseAmounts($event, $model->rsvp_id);
    }

    public function updated(Member $model): void
    {
        $event = $model->event;
        $user  = $model->user;

        if ($model->isDirty(['rsvp_id'])) {
            $oldRsvp = $model->getOriginal('rsvp_id');
            $this->decreaseAmounts($event, $oldRsvp);
            $this->increaseAmounts($event, $model->rsvp_id);

            if ($user instanceof User && $model->hasMemberPrivileges()) {
                $this->inviteRepository()->handleJoinEvent($event->entityId(), $user);
            }
        }
    }

    private function decreaseAmounts(Event $event, int $rsvp): void
    {
        switch ($rsvp) {
            case Member::JOINED:
                if ($event instanceof HasTotalMember) {
                    $event->decrementAmount('total_member');
                }
                break;
            case Member::INTERESTED:
                if ($event instanceof HasTotalInterested) {
                    $event->decrementAmount('total_interested');
                }
                break;
        }
    }

    private function increaseAmounts(Event $event, int $rsvp): void
    {
        switch ($rsvp) {
            case Member::JOINED:
                if ($event instanceof HasTotalMember) {
                    $event->incrementAmount('total_member');
                }
                break;
            case Member::INTERESTED:
                if ($event instanceof HasTotalInterested) {
                    $event->incrementAmount('total_interested');
                }
                break;
        }
    }
}

// end stub
