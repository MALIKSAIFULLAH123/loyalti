<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use MetaFox\Platform\PackageManager;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Broadcast::routes();
        $channels = [];
        PackageManager::withActivePackages(function ($info) use (&$channels) {
            $channel = base_path(sprintf("%s/routes/channels.php", $info['path']));
            if (File::exists($channel)) {
                $channels[] = $channel;
            }
        });

        foreach ($channels as $channel) {
            require($channel);
        }
    }
}
