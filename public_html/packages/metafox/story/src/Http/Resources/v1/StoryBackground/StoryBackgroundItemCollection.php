<?php

namespace MetaFox\Story\Http\Resources\v1\StoryBackground;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class StoryBackgroundItemCollection extends ResourceCollection
{
    public bool $preserveKeys = true;
    public $collects          = StoryBackgroundItem::class;
}
