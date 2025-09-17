<?php

namespace MetaFox\Friend\Http\Resources\v1\Friend;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class FriendSimpleCollection extends ResourceCollection
{
    public $collects = FriendSimple::class;
}
