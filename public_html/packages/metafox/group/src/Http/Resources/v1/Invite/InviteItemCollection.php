<?php

namespace MetaFox\Group\Http\Resources\v1\Invite;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class InviteItemCollection.
 */
class InviteItemCollection extends ResourceCollection
{
    public $collects = InviteItem::class;
}
