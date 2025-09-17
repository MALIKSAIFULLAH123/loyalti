<?php

namespace MetaFox\Photo\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Core\Constants;
use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\PackageManager;

class ProxyItemListener
{
    public function handle(Entity $entity): ?array
    {
        if (!$entity instanceof PhotoGroup) {
            return null;
        }

        $returnThis = false;

        if ($entity->total_item > 1) {
            $returnThis = true;
        }

        $items = $entity->items()->get();

        if ($items->count() > 1) {
            $returnThis = true;
        }

        if ($items->count() == 0) {
            $returnThis = true;
        }

        $item = $items->first();

        if (null === $item->detail) {
            $returnThis = true;
        }

        $data = [
            'alternative_item_type' => $returnThis ? $entity->entityType() : $item->detail->entityType(),
            'alternative_item_id'   => $returnThis ? $entity->entityId() : $item->detail->entityId(),
        ];

        [, , , $alternatePackageId]         = app('core.drivers')->loadDriver(Constants::DRIVER_TYPE_ENTITY, Arr::get($data, 'alternative_item_type', ''));
        $data['alternative_item_module_id'] = PackageManager::getAlias($alternatePackageId ?: 'photo');

        return $data;
    }
}
