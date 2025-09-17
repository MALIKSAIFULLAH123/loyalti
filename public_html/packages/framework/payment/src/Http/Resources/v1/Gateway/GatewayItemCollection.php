<?php

namespace MetaFox\Payment\Http\Resources\v1\Gateway;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class GatewayItemCollection extends ResourceCollection
{
    public $collects = GatewayItem::class;
}
