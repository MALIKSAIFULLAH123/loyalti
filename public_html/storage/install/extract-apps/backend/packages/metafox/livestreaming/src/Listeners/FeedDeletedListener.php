<?php

namespace MetaFox\LiveStreaming\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\LiveStreaming\Models\LiveVideo;

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
        if ($model instanceof LiveVideo) {
            $model->delete();
        }
    }
}
