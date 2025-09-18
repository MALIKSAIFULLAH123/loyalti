<?php

namespace MetaFox\Socialite\Providers;

use MetaFox\Socialite\Support\Facebook;
use SocialiteProviders\Manager\SocialiteWasCalled;

class FacebookServiceProvider
{
    /**
     * Register the provider.
     *
     * @param SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('facebook', Facebook::class);
    }
}
