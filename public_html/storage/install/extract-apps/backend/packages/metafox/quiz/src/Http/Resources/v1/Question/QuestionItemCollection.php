<?php

namespace MetaFox\Quiz\Http\Resources\v1\Question;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class QuestionItemCollection extends ResourceCollection
{
    public $collects = QuestionItem::class;
}
