<?php

namespace MetaFox\Group\Http\Resources\v1\Answer;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class AnswersItemCollection.
 */
class AnswersItemCollection extends ResourceCollection
{
    public $collects = AnswersItem::class;
}
