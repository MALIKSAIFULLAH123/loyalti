<?php

namespace MetaFox\Saved\Listeners;

use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Saved\Repositories\SavedSearchRepositoryInterface;

class ModelUpdatedListener
{
    public function handle($model): void
    {
        if ($model instanceof HasSavedItem) {
            resolve(SavedSearchRepositoryInterface::class)->updatedBy($model);
        }
    }
}
