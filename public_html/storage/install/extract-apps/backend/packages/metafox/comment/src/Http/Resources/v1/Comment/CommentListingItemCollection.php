<?php

namespace MetaFox\Comment\Http\Resources\v1\Comment;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class CommentListingItemCollection extends ResourceCollection
{
    public $collects = CommentListingItem::class;
}
