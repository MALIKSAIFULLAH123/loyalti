<?php

namespace MetaFox\Report\Http\Resources\v1\ReportReason;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class ReportReasonItemCollection extends ResourceCollection
{
    public $collects = ReportReasonItem::class;
}
