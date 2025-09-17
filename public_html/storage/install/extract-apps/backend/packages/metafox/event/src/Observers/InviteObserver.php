<?php

namespace MetaFox\Event\Observers;

use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Invite as Model;

/**
 * Class InviteObserver.
 */
class InviteObserver
{
    public function created(Model $model): void
    {
    }

    public function deleted(Model $model): void
    {
        $event = $model->event;
        if (!$event instanceof Event) {
            return;
        }

        if ($model->isPending()) {
            $event->decrementAmount('total_pending_invite');
        }
    }

    public function updated(Model $model): void
    {
        $event = $model->event;
        if ($model->isDirty(['status_id'])) {
            $newStatus = $model->status_id;
            $oldStatus = $model->getOriginal('status_id');

            if ($newStatus == Model::STATUS_PENDING) {
                $event->incrementAmount('total_pending_invite');
            }

            if ($oldStatus == Model::STATUS_PENDING) {
                $event->decrementAmount('total_pending_invite');
            }
        }
    }
}
