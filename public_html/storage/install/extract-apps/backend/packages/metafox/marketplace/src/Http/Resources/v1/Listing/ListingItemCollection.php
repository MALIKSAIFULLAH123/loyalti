<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Listing;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class ListingItemCollection extends ResourceCollection
{
    public $collects = ListingItem::class;
}
