<?php

namespace MetaFox\Event\Http\Resources\v1\Category;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class CategoryItemCollection extends ResourceCollection
{
    public $collects = CategoryItem::class;
}
