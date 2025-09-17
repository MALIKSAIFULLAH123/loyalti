<?php

namespace MetaFox\Blog\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Blog\Models\Blog;

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
        if ($model instanceof Blog) {
            $model->delete();
        }
    }
}
