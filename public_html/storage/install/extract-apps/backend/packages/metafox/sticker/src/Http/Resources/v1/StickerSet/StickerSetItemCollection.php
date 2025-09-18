<?php

namespace MetaFox\Sticker\Http\Resources\v1\StickerSet;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class StickerSetItemCollection extends ResourceCollection
{
    public $collects = StickerSetItem::class;
}
