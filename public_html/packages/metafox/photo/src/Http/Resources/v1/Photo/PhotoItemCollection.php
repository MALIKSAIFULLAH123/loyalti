<?php

namespace MetaFox\Photo\Http\Resources\v1\Photo;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PhotoItemCollection extends ResourceCollection
{
    public $collects = PhotoItem::class;
}
