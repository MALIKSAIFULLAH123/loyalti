<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Activity\Models\Post;
use MetaFox\Activity\Models\Share;

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
        if ($model instanceof Post) {
            $model->delete();
            $model->shares()->delete();
        }

        if ($model instanceof Share) {
            $model->delete();
        }
    }
}
