<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PageSimpleCollection extends ResourceCollection
{
    public $collects = PageSimple::class;
}
