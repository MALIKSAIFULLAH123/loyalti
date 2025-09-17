<?php

namespace MetaFox\User\Http\Resources\v1\User\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class UserItemCollection extends ResourceCollection
{
    public $collects = UserItem::class;
}
