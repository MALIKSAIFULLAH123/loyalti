<?php

namespace MetaFox\Activity\Observers;

use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Share;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTotalShare;

/**
 * Class ShareObserver.
 */
class ShareObserver
{
    public function created(Share $share): void
    {
        $item = $share->item;

        if ($item instanceof Feed) {
            if ($item->item instanceof HasTotalShare) {
                $item->item->incrementAmount('total_share');
            }
        }

        if ($item instanceof HasTotalShare) {
            $item->incrementAmount('total_share');
        }

        $this->handleContextItemForCreate($share);

        $this->redundantFeed($item);
    }

    private function handleContextItemForCreate(Share $share): void
    {
        /**
         * @var Entity $contextItem
         * @var Entity $item
         */
        $item = $share->item;
        $contextItem = $share->context_item;

        if (!$contextItem instanceof HasTotalShare) {
            return;
        }

        if ($contextItem->entityId() == $item->entityId() && $contextItem->entityType() == $item->entityType()) {
            return;
        }

        $contextItem->incrementAmount('total_share');

        if ($contextItem instanceof Feed && $contextItem->item instanceof HasTotalShare && $contextItem->item->entityType() != $item->entityType() && $contextItem->item->entityId() != $item->entityId()) {
            $contextItem->item->incrementAmount('total_share');
        }
    }

    private function handleContextItemForDelete(Share $share): void
    {
        /**
         * @var Entity $contextItem
         * @var Entity $item
         */
        $item = $share->item;
        $contextItem = $share->context_item;

        if (!$contextItem instanceof HasTotalShare) {
            return;
        }

        if ($contextItem->entityId() == $item->entityId() && $contextItem->entityType() == $item->entityType()) {
            return;
        }

        $contextItem->decrementAmount('total_share');

        if ($contextItem instanceof Feed && $contextItem->item instanceof HasTotalShare && $contextItem->item->entityType() != $item->entityType() && $contextItem->item->entityId() != $item->entityId()) {
            $contextItem->item->decrementAmount('total_share');
        }
    }

    public function deleted(Share $share): void
    {
        $item = $share->item;

        if ($item instanceof Feed) {
            if ($item->item instanceof HasTotalShare) {
                $item->item->decrementAmount('total_share');
            }
        }

        if ($item instanceof HasTotalShare) {
            $item->decrementAmount('total_share');
        }

        $this->handleContextItemForDelete($share);

        $this->redundantFeed($item);
    }

    private function redundantFeed(?Entity $item): void
    {
        app('events')->dispatch('activity.redundant', [$item], true);
    }
}
