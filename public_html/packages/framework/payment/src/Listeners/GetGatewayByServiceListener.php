<?php

namespace MetaFox\Payment\Listeners;

use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;

class GetGatewayByServiceListener
{
    public function handle(string $service): ?Gateway
    {
        return resolve(GatewayRepositoryInterface::class)->getGatewayByService($service);
    }
}
