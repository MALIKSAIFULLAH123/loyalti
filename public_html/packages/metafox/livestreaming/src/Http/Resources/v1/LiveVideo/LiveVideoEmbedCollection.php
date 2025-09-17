<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class LiveVideoEmbedCollection extends ResourceCollection
{
    public $collects = LiveVideoEmbed::class;
}
