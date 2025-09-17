<?php

namespace MetaFox\Authorization\Http\Resources\v1\Permission;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PermissionItemCollection extends ResourceCollection
{
    public $collects = PermissionItem::class;
}
