<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Models\CollectionStatistic;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Platform\Contracts\Entity;

class IncreaseCollectionStatisticListener
{
    public function handle(Entity $group, string $entityType): ?bool
    {
        if ($group->entityType() != PhotoGroup::ENTITY_TYPE) {
            return null;
        }

        $group->incrementAmount('total_item');

        if ($group->statistic instanceof CollectionStatistic) {
            $group->statistic->incrementTotalColumn($entityType);
        }

        return true;
    }
}
