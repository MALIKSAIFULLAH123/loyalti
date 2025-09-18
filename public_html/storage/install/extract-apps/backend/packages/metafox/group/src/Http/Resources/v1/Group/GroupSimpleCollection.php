<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class GroupSimpleCollection extends ResourceCollection
{
    public $collects = GroupSimple::class;
}
