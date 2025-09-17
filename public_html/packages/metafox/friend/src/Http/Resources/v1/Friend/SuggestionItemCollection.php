<?php

namespace MetaFox\Friend\Http\Resources\v1\Friend;

use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class SuggestionItemCollection extends ResourceCollection
{
    public $collects = SuggestionItem::class;
}
