<?php

namespace MetaFox\Group\Http\Resources\v1\Member;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class MemberItemCollection.
 */
class MemberItemCollection extends ResourceCollection
{
    public $collects = MemberItem::class;
}
