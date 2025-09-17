<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class SearchSuggestionCollection extends ResourceCollection
{
    /**
     * @var string
     */
    public $collects = SearchSuggestionItem::class;
}
