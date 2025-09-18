<?php

namespace MetaFox\Socialite\Providers;

use MetaFox\Socialite\Support\Tiktok;
use SocialiteProviders\Manager\SocialiteWasCalled;

class TikTokServiceProvider
{
    /**
     * Register the provider.
     *
     * @param SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('tiktok', Tiktok::class);
    }
}
