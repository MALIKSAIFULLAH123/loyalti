<?php

namespace MetaFox\Core\Http\Resources\v1\StatsContentType\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class StatsContentTypeItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class StatsContentTypeItemCollection extends ResourceCollection
{
    public $collects = StatsContentTypeItem::class;
}
