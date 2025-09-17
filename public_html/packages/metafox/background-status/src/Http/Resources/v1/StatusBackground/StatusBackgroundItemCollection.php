<?php

namespace MetaFox\BackgroundStatus\Http\Resources\v1\StatusBackground;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class StatusBackgroundItemCollection extends ResourceCollection
{
    public $collects = StatusBackgroundItem::class;
}
