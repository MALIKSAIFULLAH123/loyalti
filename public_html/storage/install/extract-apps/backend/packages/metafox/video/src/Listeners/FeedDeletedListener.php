<?php

namespace MetaFox\Video\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Video\Models\Video;

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
        if ($model instanceof Video) {
            $model->delete();
        }
    }
}
