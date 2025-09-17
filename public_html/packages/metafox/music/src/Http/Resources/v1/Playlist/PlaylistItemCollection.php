<?php

namespace MetaFox\Music\Http\Resources\v1\Playlist;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PlaylistItemCollection extends ResourceCollection
{
    public $collects = PlaylistItem::class;
}
