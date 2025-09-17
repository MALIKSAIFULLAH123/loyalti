<?php

namespace MetaFox\BackgroundStatus\Http\Resources\v1\RecentUsed;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class RecentUsedItemCollection extends ResourceCollection
{
    public $collects = RecentUsedItem::class;
}
