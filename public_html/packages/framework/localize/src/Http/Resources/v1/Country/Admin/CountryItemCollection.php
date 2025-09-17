<?php

namespace MetaFox\Localize\Http\Resources\v1\Country\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class CountryItemCollection extends ResourceCollection
{
    public $collects = CountryItem::class;
}
