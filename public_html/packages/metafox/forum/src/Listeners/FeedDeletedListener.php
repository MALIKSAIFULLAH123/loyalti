<?php

namespace MetaFox\Forum\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Forum\Models\ForumPost;
use MetaFox\Forum\Models\ForumThread;

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
        if ($model instanceof ForumThread || $model instanceof ForumPost) {
            $model->delete();
        }
    }
}
