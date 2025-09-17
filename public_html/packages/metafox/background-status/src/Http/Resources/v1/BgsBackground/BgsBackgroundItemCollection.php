<?php

namespace MetaFox\BackgroundStatus\Http\Resources\v1\BgsBackground;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class BgsBackgroundItemCollection extends ResourceCollection
{
    public bool $preserveKeys = true;
    public $collects          = BgsBackgroundItem::class;
}
