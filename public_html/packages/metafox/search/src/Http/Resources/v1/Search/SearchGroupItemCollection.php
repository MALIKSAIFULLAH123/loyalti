<?php

namespace MetaFox\Search\Http\Resources\v1\Search;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class SearchGroupItemCollection extends ResourceCollection
{
    public $collects = SearchGroupItem::class;
}
