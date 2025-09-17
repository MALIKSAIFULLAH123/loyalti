<?php

namespace MetaFox\Socialite\Listeners;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;

/**
 * Class TiktokProviderConfigListener.
 * @ignore
 * @codeCoverageIgnore
 */
class TiktokProviderConfigListener
{
    /**
     * @param  string       $providerName
     * @param  array<mixed> $params
     * @return void
     */
    public function handle(string $providerName, array &$params = []): void
    {
        if ($providerName != 'tiktok') {
            return;
        }

        $params = Settings::get('core.services.tiktok');
    }
}
