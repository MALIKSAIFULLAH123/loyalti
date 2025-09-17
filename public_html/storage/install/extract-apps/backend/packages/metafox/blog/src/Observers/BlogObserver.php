<?php

namespace MetaFox\Blog\Observers;

use MetaFox\Blog\Models\Blog as Model;

/**
 * Class BlogObserver.
 * @ignore
 * @codeCoverageIgnore
 */
class BlogObserver
{
    /**
     * Invoked when a model is creating.
     *
     * @param Model $model
     */
    public function creating(Model $model)
    {
    }

    /**
     * Invoked when a model is created.
     *
     * @param Model $model
     */
    public function created(Model $model)
    {
    }

    /**
     * Invoked when a model is updating.
     *
     * @param Model $model
     */
    public function updating(Model $model)
    {
    }

    /**
     *Invoked when a model is updated.
     *
     * @param Model $model
     */
    public function updated(Model $model)
    {
    }

    /**
     * Invoked when a model is deleting.
     *
     * @param Model $model
     */
    public function deleting(Model $model)
    {
    }

    /**
     * Invoked when a model is deleted.
     *
     * @param Model $model
     */
    public function deleted(Model $model): void
    {
        $model->blogText()->delete();
        $model->tagData()->sync([]);
        $model->categories()->sync([]);
    }
}
