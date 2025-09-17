<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Category\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class CategoryItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class CategoryItemCollection extends ResourceCollection
{
    public $collects = CategoryItem::class;
}
