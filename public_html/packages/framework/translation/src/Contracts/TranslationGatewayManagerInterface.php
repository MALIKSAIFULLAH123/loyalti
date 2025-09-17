<?php

namespace MetaFox\Translation\Contracts;

use MetaFox\Translation\Models\TranslationGateway;

interface TranslationGatewayManagerInterface
{
    /**
     * getGatewayById.
     *
     * @param int $gatewayId
     * @return ?TranslationGateway
     */
    public function getGatewayById(int $gatewayId): ?TranslationGateway;
}
