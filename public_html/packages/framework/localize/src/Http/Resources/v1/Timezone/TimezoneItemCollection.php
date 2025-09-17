<?php

namespace MetaFox\Localize\Http\Resources\v1\Timezone;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class TimezoneItemCollection.
 */
class TimezoneItemCollection extends ResourceCollection
{
    public $collects = TimezoneItem::class;
}
