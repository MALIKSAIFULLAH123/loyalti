<?php

namespace MetaFox\Group\Http\Resources\v1\Question;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class QuestionItemCollection.
 */
class QuestionItemCollection extends ResourceCollection
{
    public bool $preserveKeys = true;

    public $collects = QuestionItem::class;
}
