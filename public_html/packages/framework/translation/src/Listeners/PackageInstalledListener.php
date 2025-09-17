<?php

namespace MetaFox\Translation\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Platform\PackageManager;
use MetaFox\Translation\Repositories\TranslationGatewayRepositoryInterface;

class PackageInstalledListener
{
    public function __construct(protected TranslationGatewayRepositoryInterface $repository)
    {
    }

    public function handle(string $package): void
    {
        $config = PackageManager::getConfig($package);

        $this->handleConfig($config);
    }

    public function handleConfig($config): void
    {
        if (!is_array($config)) {
            return;
        }

        $gatewayConfigs = Arr::get($config, 'translation_gateways', []);
        if (empty($gatewayConfigs)) {
            return;
        }

        $this->repository->setupTranslationGateways($gatewayConfigs);
    }
}
