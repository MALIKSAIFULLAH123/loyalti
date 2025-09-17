<?php

namespace MetaFox\Friend\Http\Resources\v1\Friend;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class FriendItemCollection extends ResourceCollection
{
    public $collects = FriendItem::class;
}
