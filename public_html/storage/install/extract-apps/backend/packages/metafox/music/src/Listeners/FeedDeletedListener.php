<?php

namespace MetaFox\Music\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Song;

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
        if ($model instanceof Song || $model instanceof Album) {
            $model->delete();
        }
    }
}
