<?php

namespace MetaFox\Payment\Http\Resources\v1\Gateway;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class GatewayConfigurationItemCollection extends ResourceCollection
{
    public $collects = GatewayConfigurationItem::class;
}
