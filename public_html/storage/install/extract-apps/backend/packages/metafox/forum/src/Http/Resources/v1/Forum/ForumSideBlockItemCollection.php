<?php

namespace MetaFox\Forum\Http\Resources\v1\Forum;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class ForumSideBlockItemCollection extends ResourceCollection
{
    /**
     * @var string
     */
    public $collects = ForumSideBlockItem::class;
}
