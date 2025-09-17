<?php

namespace MetaFox\Chat\Listeners;

use MetaFox\Chat\Models\Subscription;
use MetaFox\User\Models\User;

class ModelUpdatedListener
{
    public function handle($model)
    {
        if ($model instanceof User) {
            $roomIds = Subscription::query()->getModel()
                ->where(['user_id' => $model->entityId()])
                ->pluck('room_id')
                ->toArray();
            Subscription::query()->getModel()
                ->whereIn('room_id', $roomIds)
                ->whereNot('user_id', $model->entityId())
                ->update(['name' => $model->display_name]);
        }
    }
}
