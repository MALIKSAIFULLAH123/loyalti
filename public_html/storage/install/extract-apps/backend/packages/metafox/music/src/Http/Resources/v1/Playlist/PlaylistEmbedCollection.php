<?php

namespace MetaFox\Music\Http\Resources\v1\Playlist;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PlaylistEmbedCollection extends ResourceCollection
{
    /** @var string */
    protected $collect = PlaylistEmbed::class;
}
