<?php

namespace MetaFox\Music\Http\Resources\v1\Genre;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class GenreItemCollection extends ResourceCollection
{
    public $collects = GenreItem::class;
}
