<?php

namespace MetaFox\Friend\Http\Resources\v1\FriendList;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class FriendListItemCollection extends ResourceCollection
{
    public $collects = FriendListItem::class;
}
