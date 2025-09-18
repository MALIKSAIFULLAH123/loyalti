<?php

namespace MetaFox\Quiz\Http\Resources\v1\Quiz;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class QuizItemCollection extends ResourceCollection
{
    public $collects = QuizItem::class;
}
