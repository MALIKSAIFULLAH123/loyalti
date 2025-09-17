<?php

namespace MetaFox\Friend\Http\Resources\v1\FriendRequest;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PendingCollection extends ResourceCollection
{
    public $collects = PendingItem::class;
}
