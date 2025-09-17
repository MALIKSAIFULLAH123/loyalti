<?php

namespace MetaFox\Quiz\Http\Resources\v1\Result;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class ResultItemCollection extends ResourceCollection
{
    public $collects = ResultItem::class;
}
