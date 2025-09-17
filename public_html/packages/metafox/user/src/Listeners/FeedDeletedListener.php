<?php

namespace MetaFox\User\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\User\Models\UserRelationHistory;

/**
 * Class FeedDeletedListener.
 * @ignore
 */
class FeedDeletedListener
{
    /**
     * @param Model $model
     */
    public function handle(Model $model): void
    {
        if ($model instanceof UserRelationHistory) {
            $model->delete();
        }
    }
}
