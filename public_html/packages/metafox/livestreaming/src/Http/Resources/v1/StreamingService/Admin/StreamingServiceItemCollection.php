<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\StreamingService\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class StreamingServiceItemCollection extends ResourceCollection
{
    public $collects = StreamingServiceItem::class;
}
