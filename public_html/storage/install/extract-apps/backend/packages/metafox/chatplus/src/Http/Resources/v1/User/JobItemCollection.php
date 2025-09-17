<?php

namespace MetaFox\ChatPlus\Http\Resources\v1\User;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class UserItemCollection.
 * @property JobItem[] $resource
 */
class JobItemCollection extends ResourceCollection
{
    public $collects = JobItem::class;
}
