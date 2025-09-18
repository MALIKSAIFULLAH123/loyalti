<?php

namespace MetaFox\Music\Http\Resources\v1\Album;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class AlbumEmbedCollection extends ResourceCollection
{
    /** @var string */
    protected $collect = AlbumEmbed::class;
}
