<?php

namespace MetaFox\Socialite\Listeners;

use Laravel\Socialite\Facades\Socialite;
use MetaFox\Socialite\Support\Traits\SocialiteConfigTrait;

/**
 * Class SocialAccountRequestListener.
 * @ignore
 * @codeCoverageIgnore
 */
class SocialAccountRequestListener
{
    use SocialiteConfigTrait;

    /**
     * @return array<string, mixed>
     */
    public function handle(string $providerName): array
    {
        $this->configProvider($providerName);
        $provider = Socialite::driver($providerName)->stateless();

        return [
            'provider' => $provider,
            'text'     => __p("socialite::{$providerName}.signup_with"),
            'url'      => $provider->redirect()->getTargetUrl(),
        ];
    }
}
