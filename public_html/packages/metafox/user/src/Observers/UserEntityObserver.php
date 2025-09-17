<?php

namespace MetaFox\User\Observers;

use MetaFox\User\Models\UserEntity;
use MetaFox\User\Support\Facades\User;

/**
 * Class UserEntityObserver.
 */
class UserEntityObserver
{
    public function creating(UserEntity $model): void
    {
        $model->short_name = User::getShortName($model->name);
    }

    public function created(UserEntity $model): void
    {
        // coding here.
    }

    public function updating(UserEntity $model): void
    {
        if ($model->isDirty('name')) {
            $model->short_name = User::getShortName($model->name);
        }
    }

    public function updated(UserEntity $model): void
    {
        // coding here.
    }

    public function deleted(UserEntity $model): void
    {
        // coding here.
    }
}

// end stub
