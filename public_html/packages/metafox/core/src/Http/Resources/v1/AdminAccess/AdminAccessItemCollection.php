<?php

namespace MetaFox\Core\Http\Resources\v1\AdminAccess;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class AdminAccessItemCollection extends ResourceCollection
{
    public $collects = AdminAccessItem::class;
}
