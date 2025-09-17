<?php

namespace MetaFox\Blog\Listeners;

use MetaFox\Blog\Models\Blog;

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
        if (!$model instanceof Blog) {
            return;
        }

        //Prevent loop forever when using isDirty with is_draft when created
        if ($model->wasRecentlyCreated) {
            return;
        }

        if (!$model->isDirty('is_draft')) {
            return;
        }

        if (!$model->isPublished()) {
            return;
        }

        //Prevent loop forever after publishing blog
        $model->syncOriginal();

        app('events')->dispatch('models.notify.published', [$model], true);
    }
}
