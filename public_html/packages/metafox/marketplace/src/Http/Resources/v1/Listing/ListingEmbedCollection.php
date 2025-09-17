<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Listing;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class ListingEmbedCollection extends ResourceCollection
{
    /** @var string */
    protected $collect = ListingEmbed::class;
}
