<?php

namespace MetaFox\User\Http\Resources\v1\CancelReason\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class CancelReasonItemCollection extends ResourceCollection
{
    public $collects = CancelReasonItem::class;
}
