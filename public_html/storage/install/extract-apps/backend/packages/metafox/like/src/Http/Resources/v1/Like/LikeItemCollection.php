<?php

namespace MetaFox\Like\Http\Resources\v1\Like;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class LikeItemCollection extends ResourceCollection
{
    public $collects = LikeItem::class;
}
