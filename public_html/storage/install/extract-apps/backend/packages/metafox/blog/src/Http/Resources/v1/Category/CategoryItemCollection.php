<?php

namespace MetaFox\Blog\Http\Resources\v1\Category;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class CategoryItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class CategoryItemCollection extends ResourceCollection
{
    /**
     * @var string
     */
    public $collects = CategoryItem::class;
}
