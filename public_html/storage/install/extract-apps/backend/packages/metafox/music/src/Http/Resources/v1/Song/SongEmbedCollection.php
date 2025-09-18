<?php

namespace MetaFox\Music\Http\Resources\v1\Song;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class SongEmbedCollection extends ResourceCollection
{
    /** @var string */
    protected $collect = SongEmbed::class;
}
