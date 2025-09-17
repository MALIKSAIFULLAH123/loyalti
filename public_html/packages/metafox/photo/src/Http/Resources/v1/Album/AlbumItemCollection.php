<?php

namespace MetaFox\Photo\Http\Resources\v1\Album;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class AlbumItemCollection extends ResourceCollection
{
    public $collects = AlbumItem::class;
}
