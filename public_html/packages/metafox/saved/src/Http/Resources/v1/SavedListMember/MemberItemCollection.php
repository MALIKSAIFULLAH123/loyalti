<?php

namespace MetaFox\Saved\Http\Resources\v1\SavedListMember;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class MemberItemCollection extends ResourceCollection
{
    public $collects = MemberItem::class;
}
