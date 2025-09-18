<?php

namespace MetaFox\Page\Http\Resources\v1\PageCategory;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PageCategoryItemCollection extends ResourceCollection
{
    public $collects = PageCategoryItem::class;
}
