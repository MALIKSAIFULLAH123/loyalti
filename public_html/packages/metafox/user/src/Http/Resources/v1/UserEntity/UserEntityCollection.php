<?php

namespace MetaFox\User\Http\Resources\v1\UserEntity;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class UserEntityCollection extends ResourceCollection
{
    public $collects = UserEntityDetail::class;
}
