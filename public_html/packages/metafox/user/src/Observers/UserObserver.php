<?php

namespace MetaFox\User\Observers;

use Illuminate\Support\Facades\Request;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserObserver
{
    public function creating(User $model): void
    {
        if ($model->is_featured === null) {
            $model->is_featured = 0;
        }
    }

    public function created(User $model): void
    {
        $now = now();

        $model->userActivity()->create([
            'last_activity'   => $now,
            'last_login'      => $now,
            'last_ip_address' => Request::ip(),
        ]);

        if ($model->isApproved()) {
            app('events')->dispatch('sticker.add_default_sticker_for_user', [$model], true);
        }

    }

    public function updated(User $model): void
    {
        // coding here.
    }

    public function forceDeleted(User $model): void
    {
        UserEntity::forceDeleteEntity($model->entityId());
    }
}
