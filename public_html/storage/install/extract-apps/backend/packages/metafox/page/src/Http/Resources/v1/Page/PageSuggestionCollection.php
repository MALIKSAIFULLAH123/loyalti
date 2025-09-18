<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PageSuggestionCollection extends ResourceCollection
{
    public $collects = PageSuggestion::class;
}
