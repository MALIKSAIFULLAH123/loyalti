<?php

namespace MetaFox\Core\Http\Resources\v1\Link;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class LinkItemCollection extends ResourceCollection
{
    public $collects = LinkItem::class;
}
