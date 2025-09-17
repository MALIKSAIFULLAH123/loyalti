<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class SeventItemCollection extends ResourceCollection
{
    protected $collect = SeventItem::class;
}
