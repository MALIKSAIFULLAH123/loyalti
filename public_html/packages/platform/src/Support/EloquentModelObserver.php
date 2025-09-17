<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Support;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\BigNumberId;
use MetaFox\Platform\Contracts\UniqueIdInterface;

/**
 * Class EloquentModelObserver.
 */
class EloquentModelObserver
{
    /**
     * @param Model $model
     */
    public function creating(Model $model): void
    {
        // This model current is just a bulk data without id and not inserted in database yet. Please careful to use this state.
        app('events')
            ->dispatch('models.notify.creating', [$model]);

        if ($model instanceof BigNumberId && app()->bound(UniqueIdInterface::class)) {
            $model->setEntityId(resolve(UniqueIdInterface::class)->getUniqueId($model->entityType()));
        }
    }

    /**
     * @param Model $model
     */
    public function created(Model $model): void
    {
        app('events')
            ->dispatch('models.notify.created', [$model]);
    }

    /**
     * @param Model $model
     */
    public function saving(Model $model): void
    {
        app('events')
            ->dispatch('models.notify.saving', [$model]);
    }

    /**
     * @param Model $model
     */
    public function updated(Model $model): void
    {
        app('events')
            ->dispatch('models.notify.updated', [$model]);
    }

    /**
     * @param Model $model
     */
    public function updating(Model $model): void
    {
        app('events')
            ->dispatch('models.notify.updating', [$model]);
    }

    /**
     * @param Model $model
     */
    public function deleting(Model $model): void
    {
        app('events')
            ->dispatch('models.notify.deleting', [$model]);
    }

    /**
     * @param Model $model
     */
    public function deleted(Model $model): void
    {
        app('events')
            ->dispatch('models.notify.deleted', [$model]);
    }
}
