<?php

namespace MetaFox\Search\Listeners;

use MetaFox\Search\Repositories\SearchRepositoryInterface;

class UpdateSearchTextListener
{
    public function handle(string $itemType, int $itemId, array $attributes): bool
    {
        return resolve(SearchRepositoryInterface::class)->updateSearchText($itemType, $itemId, $attributes);
    }
}
