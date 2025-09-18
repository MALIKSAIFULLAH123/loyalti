<?php

namespace MetaFox\Video\Http\Resources\v1\VideoService\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class VideoServiceItemCollection extends ResourceCollection
{
    public $collects = VideoServiceItem::class;
}
