<?php

namespace MetaFox\Search\Listeners;

use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Search\Repositories\SearchRepositoryInterface;

class UpdateSearchItemListener
{
    public function handle(HasGlobalSearch $item): void
    {
        resolve(SearchRepositoryInterface::class)->updatedBy($item);
    }
}
