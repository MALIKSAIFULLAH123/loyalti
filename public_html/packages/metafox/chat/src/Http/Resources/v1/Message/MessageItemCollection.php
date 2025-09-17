<?php

namespace MetaFox\Chat\Http\Resources\v1\Message;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class MessageItemCollection extends ResourceCollection
{
    protected $collect = MessageItem::class;
}
