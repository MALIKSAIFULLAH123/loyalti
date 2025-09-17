<?php

namespace MetaFox\Blog\Http\Resources\v1\Blog\Admin;

use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class BlogItemCollection extends ResourceCollection
{
    protected $collect = BlogItem::class;
}
