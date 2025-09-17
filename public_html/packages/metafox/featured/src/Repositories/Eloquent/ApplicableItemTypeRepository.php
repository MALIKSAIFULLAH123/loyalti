<?php

namespace MetaFox\Featured\Repositories\Eloquent;

use MetaFox\Featured\Models\Package;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Featured\Repositories\ApplicableItemTypeRepositoryInterface;
use MetaFox\Featured\Models\ApplicableItemType;

/**
 * stub: /packages/repositories/eloquent_repository.stub
 */

/**
 * Class ApplicableItemTypeRepository
 *
 */
class ApplicableItemTypeRepository extends AbstractRepository implements ApplicableItemTypeRepositoryInterface
{
    public function model()
    {
        return ApplicableItemType::class;
    }

    public function updateForPackage(Package $package, array $itemTypes): bool
    {
        $current = $package->item_types->pluck('item_type')->toArray();
        $new    = array_diff($itemTypes, $current);
        $remove = array_diff($current, $itemTypes);

        if (!count($new) && !count($remove)) {
            return true;
        }

        if (count($new)) {
            $map = array_map(function ($itemType) use ($package) {
                return [
                    'item_type' => $itemType,
                    'package_id' => $package->entityId(),
                ];
            }, $new);

            $this->getModel()->newQuery()
                ->upsert($map, ['package_id', 'item_type']);
        }

        if (count($remove)) {
            $this->getModel()->newQuery()
                ->where('package_id', '=', $package->entityId())
                ->whereIn('item_type', $remove)
                ->delete();
        }

        return true;
    }

    public function deleteForPackage(Package $package): bool
    {
        $this->getModel()->newQuery()
            ->where('package_id', '=', $package->entityId())
            ->delete();
    }
}
