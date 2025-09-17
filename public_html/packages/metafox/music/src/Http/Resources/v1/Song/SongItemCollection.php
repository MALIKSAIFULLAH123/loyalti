<?php

namespace MetaFox\Music\Http\Resources\v1\Song;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class SongItemCollection extends ResourceCollection
{
    public $collects = SongItem::class;
}
