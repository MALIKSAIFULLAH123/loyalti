<?php

namespace MetaFox\Group\Http\Resources\v1\QuestionField;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class QuestionFieldItemCollection extends ResourceCollection
{
    public $collects = QuestionFieldItem::class;
}
