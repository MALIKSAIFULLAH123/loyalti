<?php

namespace MetaFox\Report\Http\Resources\v1\ReportOwner;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class ReporterCollection extends ResourceCollection
{
    /**
     * @var string
     */
    public $collects = Reporter::class;
}
