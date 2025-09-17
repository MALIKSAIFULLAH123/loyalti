<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Payment\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;
use MetaFox\Platform\PackageManager;
use Throwable;

/**
 * Class PackageInstalledListener.
 * @ignore
 */
class PackageInstalledListener
{
    public function __construct(
        protected GatewayRepositoryInterface $repository
    ) {
    }

    /**
     * @param string $package
     *
     * @throws Throwable
     */
    public function handle(string $package): void
    {
        $config = PackageManager::getConfig($package);

        $this->handleConfig($config);
    }

    /**
     * @param array<mixed> $config
     */
    public function handleConfig($config): void
    {
        if (!is_array($config)) {
            return;
        }

        $gatewayConfigs = Arr::get($config, 'gateways', []);

        if (empty($gatewayConfigs)) {
            return;
        }

        $this->repository->setupPaymentGateways($gatewayConfigs);
    }
}
