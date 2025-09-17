<?php

namespace MetaFox\Socialite\Support\Traits;

use MetaFox\Platform\Facades\Settings;

trait SocialiteConfigTrait
{
    /**
     * @param string $providerName
     *
     * @return void
     */
    protected function configProvider(string $providerName)
    {
        $config = [];
        foreach (Settings::get("core.services.$providerName") as $key => $value) {
            // avoid unnecessary overridden
            $config["services.{$providerName}.$key"] = $value;
        }

        app('events')->dispatch('socialite.provider.config', [$providerName, $config]);

        config($config);
    }
}
