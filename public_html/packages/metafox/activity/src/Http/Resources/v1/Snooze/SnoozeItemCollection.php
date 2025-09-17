<?php

namespace MetaFox\Activity\Http\Resources\v1\Snooze;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class SnoozeItemCollection extends ResourceCollection
{
    public $collects = SnoozeItem::class;
}
