<?php

namespace MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class BgsCollectionItemCollection extends ResourceCollection
{
    public bool $preserveKeys = true;
    public $collects          = BgsCollectionItem::class;
}
