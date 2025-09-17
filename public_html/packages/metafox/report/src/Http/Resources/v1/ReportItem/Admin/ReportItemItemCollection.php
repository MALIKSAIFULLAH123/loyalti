<?php

namespace MetaFox\Report\Http\Resources\v1\ReportItem\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class ReportItemItemCollection extends ResourceCollection
{
    public $collects = ReportItemItem::class;
}
