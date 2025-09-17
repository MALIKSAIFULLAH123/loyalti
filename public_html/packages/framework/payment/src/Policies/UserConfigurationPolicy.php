<?php

namespace MetaFox\Payment\Policies;

use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use MetaFox\User\Policies\UserPolicy;

class UserConfigurationPolicy
{
    public function update(User $context, User $user, Gateway $gateway): bool
    {
        if (!policy_check(UserPolicy::class, 'updateSetting', $context, $user)) {
            return false;
        }

        if (!$gateway->is_active) {
            return false;
        }

        $name = $gateway->service . '.gateway.user_form';

        $driver = resolve(DriverRepositoryInterface::class)->getDriver(Constants::DRIVER_TYPE_USER_GATEWAY_FORM, $name, MetaFox::getResolution());

        if (!class_exists($driver)) {
            return false;
        }

        return true;
    }
}
