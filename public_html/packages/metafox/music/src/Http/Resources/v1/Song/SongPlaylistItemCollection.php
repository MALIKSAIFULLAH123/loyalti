<?php

namespace MetaFox\Music\Http\Resources\v1\Song;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class SongPlaylistItemCollection extends ResourceCollection
{
    public $collects = SongPlaylistItem::class;
}
