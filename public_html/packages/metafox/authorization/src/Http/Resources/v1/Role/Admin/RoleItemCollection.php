<?php

namespace MetaFox\Authorization\Http\Resources\v1\Role\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class RoleItemCollection extends ResourceCollection
{
    public $collects = RoleItem::class;
}
