<?php

namespace MetaFox\Comment\Http\Resources\v1\Comment;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class CommentItemCollection extends ResourceCollection
{
    public $collects = CommentItem::class;
}
