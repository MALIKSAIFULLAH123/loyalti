<?php

namespace MetaFox\Marketplace\Observers;

use Exception;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;

/**
 * Class ListingObserver.
 * @ignore
 * @codeCoverageIgnore
 */
class ListingObserver
{
    /**
     * @throws Exception
     */
    public function deleted(Listing $listing): void
    {
        resolve(ListingRepositoryInterface::class)->deleteUnusedListingData($listing);
    }

    public function updated(Listing $listing): void
    {
        $this->isSoldDirty($listing);
    }

    protected function isSoldDirty(Listing $listing): void
    {
        if (!$listing->isApproved()) {
            return;
        }

        if (!$listing->isDirty(['is_sold'])) {
            return;
        }

        if ($listing->is_sold) {
            app('events')->dispatch('search.delete_item', [$listing->entityType(), $listing->entityId()]);

            return;
        }

        app('events')->dispatch('search.update_item', [$listing]);
    }
}
