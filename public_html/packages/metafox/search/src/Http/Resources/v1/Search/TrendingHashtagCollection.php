<?php

namespace MetaFox\Search\Http\Resources\v1\Search;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class TrendingHashtagCollection extends ResourceCollection
{
    public $collects = TrendingHashtagItem::class;
}
