<?php

namespace MetaFox\Poll\Http\Resources\v1\Poll\Admin;

use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PollItemCollection extends ResourceCollection
{
    public $collects = PollItem::class;
}
