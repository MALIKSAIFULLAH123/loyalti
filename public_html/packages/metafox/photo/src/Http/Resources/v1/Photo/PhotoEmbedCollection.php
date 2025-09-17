<?php

namespace MetaFox\Photo\Http\Resources\v1\Photo;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PhotoEmbedCollection extends ResourceCollection
{
    public $collects = PhotoEmbed::class;
}
