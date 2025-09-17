<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PageItemCollection extends ResourceCollection
{
    public $collects = PageItem::class;
}
