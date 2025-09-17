<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumPost;

use Illuminate\Http\Request;
use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Support\Arr;

class ForumPostItemCollection extends ResourceCollection
{
    /**
     * @var string
     */
    public $collects = ForumPostItem::class;
}
