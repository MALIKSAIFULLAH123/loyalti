<?php
namespace MetaFox\Photo\Support;

use Illuminate\Support\Collection;
use MetaFox\Photo\Contracts\PhotoGroupSupportContract;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Platform\Facades\ResourceGate;

class PhotoGroup implements PhotoGroupSupportContract
{
    public function getMediaItems(\MetaFox\Photo\Models\PhotoGroup $group, bool $isLoadForEdit = false, ?int $limit = 4): array
    {
        /**
         * @warning Please improve this by adding is_approved to photo_group_items to filter approved only
         */
        $items = $group->items;

        if (!$items->count()) {
            return [$items, 0, 0];
        }

        $items->loadMissing(['detail']);

        if ($isLoadForEdit) {
            return [$this->mappingFeedEditResource($items), $items->count(), 0];
        }

        if (!$group->isApproved() || null === $limit) {
            return [$this->mappingMediaResource($items), $items->count(), 0];
        }

        $items = $items->filter(function (PhotoGroupItem $item) {
            return $item->isApproved();
        })->values();

        $total = $items->count();

        $items = $items->take($limit);

        $remain = $total - $items->count();

        return [$this->mappingMediaResource($items), $total, $remain];
    }

    protected function mappingFeedEditResource(Collection $items): Collection
    {
        return $items->map(function (PhotoGroupItem $item) {
            $resource = ResourceGate::asResource($item->detail, 'feed_edit');

            if (null !== $resource) {
                return $resource;
            }

            return ResourceGate::asEmbed($item->detail);
        });
    }

    protected function mappingMediaResource(Collection $items): Collection
    {
        return $items->map(function (PhotoGroupItem $item) {
            return ResourceGate::asEmbed($item->detail);
        });
    }
}
