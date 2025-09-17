<?php

namespace MetaFox\Hashtag\Http\Resources\v1\Hashtag;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class HashtagItemCollection extends ResourceCollection
{
    public $collects = HashtagItem::class;
}
