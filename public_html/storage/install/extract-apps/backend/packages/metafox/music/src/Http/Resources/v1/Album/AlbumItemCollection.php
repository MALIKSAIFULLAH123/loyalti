<?php

namespace MetaFox\Music\Http\Resources\v1\Album;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use MetaFox\Music\Http\Resources\v1\Album\AlbumItem;

class AlbumItemCollection extends ResourceCollection
{
    public $collects = AlbumItem::class;
}
