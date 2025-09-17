<?php

namespace MetaFox\Chat\Observers;

use MetaFox\Chat\Models\Room as Model;

class RoomObserver
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
        $model->subscriptions()->delete();
        $model->messages()->delete();
    }
}
