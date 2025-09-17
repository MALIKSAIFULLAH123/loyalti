<?php

namespace MetaFox\Photo\Support\Traits;

use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalLike;

trait HandlePhotoGroupItemStatisticTrait
{
    private function incrementSingleItemStatInPhotoGroup(PhotoGroup $photoGroup): void
    {
        if (!$photoGroup->isSingleItemPhotoGroup()) {
            return;
        }

        $this->updateSingleItemStats($photoGroup->getFirstApprovedPhotoGroupItem(), $photoGroup->total_like, $photoGroup->total_comment);
    }

    private function resetSingleItemStatInPhotoGroup(PhotoGroup $photoGroup, ?int $itemId = null): void
    {
        if (!$photoGroup->isSingleItemPhotoGroup()) {
            return;
        }

        $this->updateSingleItemStats($photoGroup->getFirstApprovedPhotoGroupItem($itemId));
    }

    private function updateSingleItemStats(PhotoGroupItem $groupItem, int $totalLike = 0, int $totalComment = 0): void
    {
        if ($groupItem->detail instanceof HasTotalLike) {
            $groupItem->detail->update(['total_like' => $totalLike]);
        }

        if ($groupItem->detail instanceof HasTotalComment) {
            $groupItem->detail->update(['total_comment' => $totalComment]);
        }
    }
}
