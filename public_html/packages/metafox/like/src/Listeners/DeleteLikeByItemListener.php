<?php

namespace MetaFox\Like\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Like\Jobs\DeleteLikeByItemJob;
use MetaFox\Platform\Contracts\Content;

class DeleteLikeByItemListener
{
    public function handle(Model $model): void
    {
        if ($model instanceof Content) {
            DeleteLikeByItemJob::dispatch($model->entityId(), $model->entityType());
        }
    }
}
