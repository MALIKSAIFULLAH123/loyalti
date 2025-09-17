<?php

namespace MetaFox\User\Http\Resources\v1\UserGender\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class UserGenderItemCollection extends ResourceCollection
{
    public $collects = UserGenderItem::class;
}
