<?php

namespace MetaFox\User\Listeners;

use Illuminate\Support\Facades\Cache;
use Laravel\Passport\Events\RefreshTokenCreated;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class RefreshTokenCreatedListener
{
    /**
     * Handle the event.
     *
     * @param RefreshTokenCreated $event
     *
     * @return void
     */
    public function handle(RefreshTokenCreated $event)
    {
        Cache::flush();
    }
}
