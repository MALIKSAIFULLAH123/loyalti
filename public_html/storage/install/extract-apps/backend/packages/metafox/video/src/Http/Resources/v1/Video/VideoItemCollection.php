<?php

namespace MetaFox\Video\Http\Resources\v1\Video;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class VideoItemCollection extends ResourceCollection
{
    public $collects = VideoItem::class;
}
