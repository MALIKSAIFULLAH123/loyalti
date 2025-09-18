<?php

namespace MetaFox\Group\Http\Resources\v1\Category;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class CategoryItemCollection.
 */
class CategoryItemCollection extends ResourceCollection
{
    public $collects = CategoryItem::class;
}
