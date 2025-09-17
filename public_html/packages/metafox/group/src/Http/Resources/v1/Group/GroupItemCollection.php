<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class GroupItemCollection extends ResourceCollection
{
    public $collects = GroupItem::class;
}
