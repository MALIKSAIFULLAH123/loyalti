<?php

namespace MetaFox\Socialite\Providers;

use MetaFox\Socialite\Support\Google;
use SocialiteProviders\Manager\SocialiteWasCalled;

class GoogleServiceProvider
{
    /**
     * Register the provider.
     *
     * @param SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('google', Google::class);
    }
}
