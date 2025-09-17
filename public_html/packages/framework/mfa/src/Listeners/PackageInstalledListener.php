<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Mfa\Listeners;

use Exception;
use MetaFox\Mfa\Models\Service;
use MetaFox\Platform\PackageManager;
use Throwable;

/**
 * Class PackageInstalledListener.
 * @ignore
 */
class PackageInstalledListener
{
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
        if (!is_array($config) || empty($config)) {
            return;
        }

        $this->handleServices($config);
    }

    /**
     * @param array<mixed> $config
     */
    public function handleServices(array $config): void
    {
        $services = $config['mfa_services'] ?? null;
        if (empty($services)) {
            return;
        }

        foreach ($services as $service) {
            try {
                Service::query()->firstOrCreate([
                    'name' => $service['name'],
                ], $service);
            } catch (Exception $e) {
                // silent
            }
        }
    }
}
