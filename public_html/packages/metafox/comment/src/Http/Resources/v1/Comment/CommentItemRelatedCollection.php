<?php

namespace MetaFox\Comment\Http\Resources\v1\Comment;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class CommentItemRelatedCollection extends ResourceCollection
{
    public $collects = CommentItemRelated::class;
}
