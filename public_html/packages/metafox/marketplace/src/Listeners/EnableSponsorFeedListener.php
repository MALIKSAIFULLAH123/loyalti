<?php

namespace MetaFox\Marketplace\Listeners;

use MetaFox\Marketplace\Models\Listing;
use MetaFox\Marketplace\Repositories\ListingRepositoryInterface;
use MetaFox\Platform\Contracts\Content;

class EnableSponsorFeedListener
{
    public function handle(Content $content): void
    {
        if ($content->entityType() != Listing::ENTITY_TYPE) {
            return;
        }

        resolve(ListingRepositoryInterface::class)->enableFeedSponsor($content);
    }
}
