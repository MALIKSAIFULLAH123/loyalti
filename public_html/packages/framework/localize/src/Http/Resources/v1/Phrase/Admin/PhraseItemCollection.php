<?php

namespace MetaFox\Localize\Http\Resources\v1\Phrase\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PhraseItemCollection extends ResourceCollection
{
    public $collects = PhraseItem::class;
}
