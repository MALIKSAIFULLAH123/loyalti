<?php

namespace MetaFox\Event\Http\Resources\v1\HostInvite;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class InviteItemCollection extends ResourceCollection
{
    public $collects = InviteItem::class;
}
