<?php

namespace MetaFox\Translation\Contracts;

use MetaFox\Translation\Http\Resources\v1\TranslationGateway\Admin\GatewayForm;

interface ServiceInterface
{
    /**
     * getGatewayAdminFormById.
     *
     * @param int $gatewayId
     * @return ?GatewayForm
     */
    public function getGatewayAdminFormById(int $gatewayId): ?GatewayForm;
}
