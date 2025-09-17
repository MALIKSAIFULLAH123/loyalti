<?php

namespace MetaFox\Core\Repositories\Eloquent;

use MetaFox\Core\Models\ItemStatistics;
use MetaFox\Core\Repositories\ItemStatisticsRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class ItemStatistics.
 * @method ItemStatistics getModel()
 * @method ItemStatistics find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD)
 */
class ItemStatisticsRepository extends AbstractRepository implements ItemStatisticsRepositoryInterface
{
    public function model(): string
    {
        return ItemStatistics::class;
    }

    public function increaseTotal(?Content $item, string $column): void
    {
        if (!$item instanceof Content) {
            return;
        }

        $itemStatistics = $this->getItemStatistics($item);
        if (!$itemStatistics instanceof ItemStatistics) {
            $itemStatistics = $this->createItemStatistics($item);
        }

        $itemStatistics->incrementAmount($column);
    }

    public function decreaseTotal(?Content $item, string $column): void
    {
        if (!$item instanceof Content) {
            return;
        }

        $itemStatistics = $this->getItemStatistics($item);
        if (!$itemStatistics instanceof ItemStatistics) {
            return;
        }

        $itemStatistics->decrementAmount($column);
    }

    public function getItemStatistics(Content $item): ?ItemStatistics
    {
        return $this->getModel()
            ->newQuery()
            ->where([
                'item_id'   => $item->entityId(),
                'item_type' => $item->entityType(),
            ])
            ->first();
    }

    public function createItemStatistics(Content $item): ItemStatistics
    {
        return $this
            ->getModel()
            ->create([
                'item_id'   => $item->entityId(),
                'item_type' => $item->entityType(),
            ]);
    }
}
