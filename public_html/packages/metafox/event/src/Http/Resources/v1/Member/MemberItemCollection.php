<?php

namespace MetaFox\Event\Http\Resources\v1\Member;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class MemberItemCollection extends ResourceCollection
{
    public $collects = MemberItem::class;
}
