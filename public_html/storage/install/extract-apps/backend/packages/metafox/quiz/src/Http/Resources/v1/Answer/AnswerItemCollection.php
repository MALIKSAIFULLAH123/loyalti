<?php

namespace MetaFox\Quiz\Http\Resources\v1\Answer;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class AnswerItemCollection extends ResourceCollection
{
    public $collects = AnswerItem::class;
}
