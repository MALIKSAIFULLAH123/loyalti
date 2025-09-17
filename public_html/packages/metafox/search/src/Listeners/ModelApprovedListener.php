<?php

namespace MetaFox\Search\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Search\Repositories\SearchRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class ModelApprovedListener
{
    public function handle(?User $context, Model $model): void
    {
        if (!$model instanceof HasGlobalSearch) {
            return;
        }

        resolve(SearchRepositoryInterface::class)->createdBy($model);
    }
}
