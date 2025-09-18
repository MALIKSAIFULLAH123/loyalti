<?php

namespace MetaFox\Sticker\Http\Resources\v1\Sticker;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class StickerItemCollection extends ResourceCollection
{
    public $collects = StickerItem::class;
}
