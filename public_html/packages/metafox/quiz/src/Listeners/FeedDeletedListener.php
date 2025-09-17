<?php

namespace MetaFox\Quiz\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Quiz\Models\Quiz;

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
        if ($model instanceof Quiz) {
            $model->delete();
        }
    }
}
