<?php

namespace MetaFox\Localize\Http\Resources\v1\Currency\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class CurrencyItemCollection extends ResourceCollection
{
    public $collects = CurrencyItem::class;
}
