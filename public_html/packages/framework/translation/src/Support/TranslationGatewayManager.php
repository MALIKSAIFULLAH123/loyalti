<?php

namespace MetaFox\Translation\Support;

use MetaFox\Translation\Contracts\TranslationGatewayManagerInterface;
use MetaFox\Translation\Models\TranslationGateway;

class TranslationGatewayManager implements TranslationGatewayManagerInterface
{
    public function getGatewayById(int $gatewayId): ?TranslationGateway
    {
        return TranslationGateway::find($gatewayId);
    }
}
