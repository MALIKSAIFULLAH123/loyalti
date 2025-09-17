<?php

namespace MetaFox\Chat\Http\Resources\v1\Message;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class RelatedMessageItemCollection extends ResourceCollection
{
    protected $collect = RelatedMessageItem::class;
}
