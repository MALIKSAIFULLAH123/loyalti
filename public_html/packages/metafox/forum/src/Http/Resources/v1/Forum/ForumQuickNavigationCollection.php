<?php

namespace MetaFox\Forum\Http\Resources\v1\Forum;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class ForumQuickNavigationCollection extends ResourceCollection
{
    /**
     * @var string
     */
    public $collects = ForumQuickNavigationItem::class;
}
