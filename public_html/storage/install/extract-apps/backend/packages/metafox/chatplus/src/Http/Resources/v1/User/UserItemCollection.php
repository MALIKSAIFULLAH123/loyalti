<?php

namespace MetaFox\ChatPlus\Http\Resources\v1\User;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use MetaFox\User\Models\User;

/**
 * Class UserItemCollection.
 * @property User $resource
 */
class UserItemCollection extends ResourceCollection
{
    public $collects = UserItem::class;
}
