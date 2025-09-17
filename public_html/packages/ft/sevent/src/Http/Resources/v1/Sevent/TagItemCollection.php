<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class TagItemCollection extends ResourceCollection
{
    public $collects = TagItem::class;
}
