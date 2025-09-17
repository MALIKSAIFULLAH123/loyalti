<?php

namespace MetaFox\Group\Http\Resources\v1\Request;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class RequestItemCollection.
 */
class RequestItemCollection extends ResourceCollection
{
    public $collects = RequestItem::class;
}
