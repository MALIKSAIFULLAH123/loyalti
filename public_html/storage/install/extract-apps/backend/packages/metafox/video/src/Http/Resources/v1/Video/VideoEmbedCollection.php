<?php

namespace MetaFox\Video\Http\Resources\v1\Video;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class VideoEmbedCollection extends ResourceCollection
{
    public $collects = VideoEmbed::class;
}
