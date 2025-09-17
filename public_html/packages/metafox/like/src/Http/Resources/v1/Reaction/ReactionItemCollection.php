<?php

namespace MetaFox\Like\Http\Resources\v1\Reaction;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class ReactionItemCollection extends ResourceCollection
{
    public $collects = ReactionItem::class;
}
