<?php

namespace MetaFox\Payment\Listeners;

use MetaFox\Payment\Repositories\UserConfigurationRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class UserConfigurationListener
{
    public function handle(int $userId, string $serviceName): ?array
    {
        return resolve(UserConfigurationRepositoryInterface::class)->getConfiguration($userId, $serviceName);
    }
}
