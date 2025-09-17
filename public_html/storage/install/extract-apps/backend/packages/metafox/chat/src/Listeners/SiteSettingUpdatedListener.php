<?php

namespace MetaFox\Chat\Listeners;

use Illuminate\Support\Facades\Artisan;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class SiteSettingUpdatedListener
{
    public function handle($module)
    {
        if ($module !== 'chat') {
            return;
        }

        Artisan::call('queue:restart');
    }
}
