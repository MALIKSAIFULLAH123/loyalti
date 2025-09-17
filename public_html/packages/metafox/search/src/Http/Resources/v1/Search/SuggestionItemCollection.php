<?php

namespace MetaFox\Search\Http\Resources\v1\Search;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class SuggestionItemCollection extends ResourceCollection
{
    public $collects = SuggestionItem::class;
}
