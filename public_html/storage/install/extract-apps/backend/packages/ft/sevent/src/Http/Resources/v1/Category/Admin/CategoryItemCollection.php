<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Category\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class CategoryItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class CategoryItemCollection extends ResourceCollection
{
    protected string $collect = CategoryItem::class;
}
