<?php

namespace MetaFox\Page\Http\Resources\v1\PageCategory\Admin;

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
