<?php

namespace MetaFox\Comment\Http\Resources\v1\CommentHistory;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class CommentHistoryCollection extends ResourceCollection
{
    public $collects = CommentHistoryItem::class;
}
