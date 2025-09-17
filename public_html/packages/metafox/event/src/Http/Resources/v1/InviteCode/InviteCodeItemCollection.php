<?php

namespace MetaFox\Event\Http\Resources\v1\InviteCode;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class InviteCodeItemCollection extends ResourceCollection
{
    public $collects = InviteCodeItem::class;
}
