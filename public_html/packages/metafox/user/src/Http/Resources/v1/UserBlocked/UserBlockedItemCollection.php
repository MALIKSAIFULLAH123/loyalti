<?php

namespace MetaFox\User\Http\Resources\v1\UserBlocked;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class UserBlockedItemCollection extends ResourceCollection
{
    public $collects = UserBlockedItem::class;
}
