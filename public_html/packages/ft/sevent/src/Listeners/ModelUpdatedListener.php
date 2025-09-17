<?php

namespace Foxexpert\Sevent\Listeners;

use Foxexpert\Sevent\Models\Sevent;

/**
 * Class ModelUpdatedListener.
 * @ignore
 * @codeCoverageIgnore
 * TODO: move method to observer
 */
class ModelUpdatedListener
{
    /**
     * @param  mixed $model
     * @return void
     */
    public function handle($model): void
    {
        if (!$model instanceof Sevent) {
            return;
        }

        //Prsevent loop forever when using isDirty with is_draft when created
        if ($model->wasRecentlyCreated) {
            return;
        }

        if (!$model->isDirty('is_draft')) {
            return;
        }

        if (!$model->isPublished()) {
            return;
        }

        //Prsevent loop forever after publishing sevent
        $model->syncOriginal();

        app('sevents')->dispatch('models.notify.published', [$model], true);
    }
}
