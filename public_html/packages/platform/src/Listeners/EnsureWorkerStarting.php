<?php

namespace MetaFox\Platform\Listeners;

use Laravel\Octane\Events\WorkerStarting;
use MetaFox\Platform\Facades\Profiling;

class EnsureWorkerStarting
{
    public function handle(WorkerStarting $event)
    {
        // cached singleton objects
        $app       = $event->app;
        $providers = $app->getLoadedProviders();

        $needSingletons = [];

        foreach ($providers as $className => $_) {
            $provider = $app->getProvider($className);
            if ($provider && property_exists($provider, 'singletons')) {
                foreach ($provider->singletons as $name => $value) {
                    $needSingletons[$name] = $value;
                }
            }
        }

        foreach ($needSingletons as $name => $value) {
            Profiling::log('make ' . $name);

            $app->make($name);
        }
    }
}
