<?php

namespace MetaFox\Localize\Http\Resources\v1\CountryChild\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class CountryChildItemCollection extends ResourceCollection
{
    public $collects = CountryChildItem::class;
}
