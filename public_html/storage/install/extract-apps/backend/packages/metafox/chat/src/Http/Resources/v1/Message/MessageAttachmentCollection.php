<?php

namespace MetaFox\Chat\Http\Resources\v1\Message;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class MessageAttachmentCollection extends ResourceCollection
{
    public $collects = MessageAttachmentItem::class;
}
