<?php

namespace MetaFox\Photo\Observers;

use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Models\PhotoGroupItem;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Photo\Support\Traits\HandlePhotoGroupItemStatisticTrait;

/**
 * Class PhotoGroupItemObserver.
 */
class PhotoGroupItemObserver
{
    use HandlePhotoGroupItemStatisticTrait;

    /**
     * @param  PhotoGroupItem|null $groupItem
     * @return void
     */
    public function creating(?PhotoGroupItem $groupItem): void
    {
        if (!$groupItem) {
            return;
        }

        $photoGroup = $groupItem->group;

        if (!$groupItem?->detail->isApproved()) {
            return;
        }

        $this->resetSingleItemStatInPhotoGroup($photoGroup);
    }

    /**
     * @param  PhotoGroupItem|null $groupItem
     * @return void
     */
    public function created(?PhotoGroupItem $groupItem): void
    {
        if (!$groupItem) {
            return;
        }

        if (!$groupItem?->detail->isApproved()) {
            return;
        }

        $groupItem->group?->increaseStatisticAmount($groupItem->item_type);
    }

    public function updated(?PhotoGroupItem $groupItem): void
    {
        if (!$groupItem) {
            return;
        }

        if (!$groupItem?->detail->isApproved()) {
            return;
        }

        if (!$groupItem->isDirty('group_id')) {
            return;
        }

        $groupItem?->group?->increaseStatisticAmount($groupItem->itemType());

        try {
            $oldGroup = resolve(PhotoGroupRepositoryInterface::class)->find($groupItem->getOriginal('group_id'));
            if ($oldGroup instanceof PhotoGroup) {
                $oldGroup->decreaseStatisticAmount($groupItem->itemType());
            }
        } catch (\Exception $exception) {
            // Silent the error
        }
    }

    /**
     * @param  PhotoGroupItem|null $groupItem
     * @return void
     */
    public function deleted(?PhotoGroupItem $groupItem): void
    {
        if (!$groupItem) {
            return;
        }

        $photoGroup = $groupItem->group;

        if (!$photoGroup instanceof PhotoGroup) {
            return;
        }

        $photoGroup->decreaseStatisticAmount($groupItem->itemType());

        $photoGroup->refresh();

        if (!$photoGroup->isSingleItemPhotoGroup()) {
            return;
        }

        $this->incrementSingleItemStatInPhotoGroup($photoGroup);

        if ($groupItem->detail) {
            app('events')->dispatch('comment.delete_by_item', [$groupItem->detail]);
        }
    }
}
