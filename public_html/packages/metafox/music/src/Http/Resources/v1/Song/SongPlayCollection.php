<?php

namespace MetaFox\Music\Http\Resources\v1\Song;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class SongPlayCollection extends ResourceCollection
{
    public $collects = SongPlayItem::class;
}
