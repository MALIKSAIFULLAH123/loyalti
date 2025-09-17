<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Category;

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
    protected string $collect = CategoryItem::class;
}
