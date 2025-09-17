<?php

namespace MetaFox\Translation\Http\Resources\v1\TranslationGateway;

use MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class TranslationGatewayItemCollection extends ResourceCollection
{
    public $collects = TranslationGatewayItem::class;
}
