<?php

namespace MetaFox\Event\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Event\Models\Event;

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
        if ($model instanceof Event) {
            $model->delete();
        }
    }
}
