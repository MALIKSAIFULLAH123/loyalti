<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Contracts\Content;

class ModelPendingListener
{
    public function handle(Model $model): void
    {
        if ($model instanceof Feed) {
            $response = $model->toPendingNotification();
            if (is_array($response)) {
                Notification::send(...$response);
            }
        }

        $item = $model->item;

        if (!$item instanceof Content) {
            return;
        }

        if (null === $item->owner) {
            return;
        }

        if (!$item->owner->hasPendingMode()) {
            return;
        }

        if (!$item->owner->isPendingMode()) {
            return;
        }

        $item->update(['is_approved' => $model->isApproved()]);
    }
}
