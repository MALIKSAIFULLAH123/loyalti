<?php

namespace MetaFox\Socialite\Listeners;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Socialite\Support\Facades\Apple;

/**
 * Class AppleProviderConfigListener.
 * @ignore
 * @codeCoverageIgnore
 */
class AppleProviderConfigListener
{
    /**
     * @param  string       $providerName
     * @param  array<mixed> $params
     * @return void
     */
    public function handle(string $providerName, array &$params = []): void
    {
        if ($providerName != 'apple') {
            return;
        }

        if ($this->verifyServiceParameters($params)) {
            // valid, no need to regenerate
            return;
        }

        // regenerate service & override
        Arr::set($params, 'services.apple.client_secret', $this->regenerateSecret());
    }

    /**
     * @param array<mixed> $params
     *
     * @return bool
     */
    private function verifyServiceParameters($params = []): bool
    {
        $now        = Carbon::now();
        $secret     = Arr::get($params, 'services.apple.client_secret');
        $expiration = Arr::get($params, 'services.apple.client_secret_expiration');

        if (empty($secret)) {
            return false;
        }

        if (empty($expiration)) {
            // valid forever
            return true;
        }

        return $expiration > $now->getTimestamp();
    }

    /**
     * @return string
     */
    private function regenerateSecret(): string
    {
        $settings = Settings::get('core.services.apple');
        $secret   = Apple::generateClientSecret($settings);

        Settings::save([
            'core.services.apple' => array_merge($settings, $secret),
        ]);

        return $secret['client_secret'];
    }
}
