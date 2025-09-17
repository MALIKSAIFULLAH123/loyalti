<?php

namespace MetaFox\User\Http\Resources\v1\UserEntity;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class UserEntityItemCollection extends ResourceCollection
{
    public $collects = UserEntityItem::class;
}
