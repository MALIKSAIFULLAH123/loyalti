<?php

namespace MetaFox\Quiz\Http\Resources\v1\ResultDetail;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class ResultDetailItemCollection extends ResourceCollection
{
    public $collects = ResultDetailItem::class;
}
