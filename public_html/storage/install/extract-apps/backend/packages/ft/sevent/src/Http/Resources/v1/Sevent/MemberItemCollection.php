<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class MemberItemCollection extends ResourceCollection
{
    public $collects = MemberItem::class;
}
