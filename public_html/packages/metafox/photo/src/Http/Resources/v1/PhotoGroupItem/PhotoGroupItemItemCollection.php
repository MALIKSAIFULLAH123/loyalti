<?php

namespace MetaFox\Photo\Http\Resources\v1\PhotoGroupItem;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PhotoGroupItemItemCollection extends ResourceCollection
{
    /**
     * @var string
     */
    protected $collect = PhotoGroupItemItem::class;
}
