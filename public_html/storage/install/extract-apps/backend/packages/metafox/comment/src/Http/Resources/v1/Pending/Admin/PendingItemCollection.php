<?php

namespace MetaFox\Comment\Http\Resources\v1\Pending\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PendingItemCollection extends ResourceCollection
{
    public $collects = PendingItem::class;
}
