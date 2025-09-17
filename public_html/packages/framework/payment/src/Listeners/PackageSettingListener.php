<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Payment\Listeners;

use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Policies\GatewayPolicy;
use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Platform\UserRole;

/**
 * Class PackageSettingListener.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    public function getPolicies(): array
    {
        return [
            Gateway::class => GatewayPolicy::class,
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            Gateway::ENTITY_TYPE => [
                'view'   => UserRole::LEVEL_REGISTERED,
                'update' => UserRole::LEVEL_ADMINISTRATOR,
            ],
        ];
    }

    public function getSiteSettings(): array
    {
        return [];
    }

    public function getEvents(): array
    {
        return [
            'packages.installed' => [
                PackageInstalledListener::class,
            ],
            'payment.user.configuration' => [
                UserConfigurationListener::class,
            ],
            'payment.gateway.get' => [
                GetGatewayByServiceListener::class,
            ],
            'payment.user_configuration.has_access' => [
                HasAccessUserConfigurationListener::class,
            ],
            'payment.has_payment_gateway' => [
                HasPaymentGatewayListener::class,
            ],
        ];
    }
}
