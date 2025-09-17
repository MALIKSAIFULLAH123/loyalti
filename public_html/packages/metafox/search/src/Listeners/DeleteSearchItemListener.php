<?php

namespace MetaFox\Search\Listeners;

use MetaFox\Search\Repositories\SearchRepositoryInterface;

class DeleteSearchItemListener
{
    public function handle(string $itemType, int $itemId): void
    {
        resolve(SearchRepositoryInterface::class)->deletedByItem($itemType, $itemId);
    }
}
