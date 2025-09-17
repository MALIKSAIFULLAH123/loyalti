<?php

namespace MetaFox\Chat\Http\Resources\v1\Room;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class RoomItemCollection extends ResourceCollection
{
    protected $collect = RoomItem::class;
}
