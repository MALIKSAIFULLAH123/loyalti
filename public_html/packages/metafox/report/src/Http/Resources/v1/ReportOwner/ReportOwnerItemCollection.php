<?php

namespace MetaFox\Report\Http\Resources\v1\ReportOwner;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class ReportOwnerItemCollection extends ResourceCollection
{
    public $collects = ReportOwnerItem::class;
}
