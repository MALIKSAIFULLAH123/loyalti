<?php

namespace MetaFox\Music\Http\Resources\v1\Genre;

use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class GenreEmbedCollection extends ResourceCollection
{
    public $collects = GenreEmbed::class;
}
