<?php

namespace MetaFox\Payment\Listeners;

use MetaFox\Payment\Repositories\UserConfigurationRepositoryInterface;

class HasAccessUserConfigurationListener
{
    public function handle(int $userId, int $gatewayId): bool
    {
        return resolve(UserConfigurationRepositoryInterface::class)->hasAccess($userId, $gatewayId);
    }
}
