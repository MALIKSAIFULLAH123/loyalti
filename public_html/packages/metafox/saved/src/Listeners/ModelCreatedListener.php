<?php

namespace MetaFox\Saved\Listeners;

use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Saved\Repositories\SavedSearchRepositoryInterface;

class ModelCreatedListener
{
    public function handle($model): void
    {
        if ($model instanceof HasSavedItem) {
            resolve(SavedSearchRepositoryInterface::class)->createdBy($model);
        }
    }
}
