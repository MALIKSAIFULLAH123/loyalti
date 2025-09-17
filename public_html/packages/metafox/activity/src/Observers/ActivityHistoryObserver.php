<?php

namespace MetaFox\Activity\Observers;

use MetaFox\Activity\Models\ActivityHistory;

class ActivityHistoryObserver
{
    public function created(ActivityHistory $model): void
    {
        app('events')->dispatch('hashtag.create_hashtag', [$model->user, $model, $model->content], true);
    }

    public function deleted(ActivityHistory $model): void
    {
        $model->tagData()->sync([]);
    }
}
