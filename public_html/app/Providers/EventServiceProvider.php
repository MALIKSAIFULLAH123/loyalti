<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use MetaFox\Platform\PackageManager;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [];

    public function shouldDiscoverEvents()
    {
        return true;
    }

    public function discoverEvents()
    {
        $results = [];

        foreach (PackageManager::getEvents() as $event => $listeners) {
            $results[$event] = $listeners;
        }

        return $results;
    }
}
