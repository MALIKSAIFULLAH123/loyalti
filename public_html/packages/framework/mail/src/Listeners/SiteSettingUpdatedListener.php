<?php

namespace MetaFox\Mail\Listeners;

use Illuminate\Support\Facades\Artisan;

/**
 * @ignore
 * @codeCoverageIgnore
 */
class SiteSettingUpdatedListener
{
    public function handle($module)
    {
        if ($module === 'mail') {
            Artisan::call('queue:restart');
        }
    }
}
