<?php

namespace MetaFox\Event\Http\Resources\v1\Event\Admin;

use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class EventItemCollection extends ResourceCollection
{
    public $collects = EventItem::class;
}
