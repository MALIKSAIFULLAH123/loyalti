<?php

namespace MetaFox\LiveStreaming\Listeners;

use Illuminate\Support\Facades\Log;
use MetaFox\LiveStreaming\Models\StreamingService;
use MetaFox\Platform\PackageManager;

class PackageInstalledListener
{
    /**
     * @param  string $package
     * @return void
     */
    public function handle(string $package): void
    {
        $this->seedStreamingServices($package);
    }

    private function seedStreamingServices(string $package): void
    {
        $defaultProvider = env('MFOX_LIVESTREAMING_SERVICE', 'mux');
        if ($package === 'metafox/livestreaming') {
            return;
        } else {
            $config = PackageManager::getConfig($package);
        }
        $providers = $config['livestreaming_service_providers'] ?? [];
        if (!count($providers)) {
            return;
        }
        Log::channel('installation')->info('Seeding Streaming - Config', $providers);
        foreach ($providers as $driver => $provider) {
            $provider['is_active'] = 1;
            if ($defaultProvider == $driver) {
                $provider['is_default'] = 1;
            } elseif (!class_exists($provider['service_class'] ?? null)) {
                continue;
            }
            StreamingService::query()->updateOrCreate(['driver' => $driver], $provider);
        }
    }
}
