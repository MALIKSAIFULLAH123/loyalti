<?php

namespace MetaFox\Poll\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Poll\Models\Poll;

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
        if ($model instanceof Poll) {
            $model->delete();
        }
    }
}
