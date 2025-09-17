<?php

namespace MetaFox\Comment\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Comment\Jobs\DeleteCommentByItemJob;
use MetaFox\Comment\Jobs\DeleteCommentJob;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

class ModelDeletingListener
{
    /**
     * @param Model $model
     */
    public function handle(Model $model): void
    {
        if ($model instanceof User) {
            DeleteCommentJob::dispatch($model->entityId(), $model->entityType(), 'user_id', 'user_type');
        }

        if ($model instanceof Entity) {
            DeleteCommentByItemJob::dispatch($model->entityId(), $model->entityType());
        }
    }
}
