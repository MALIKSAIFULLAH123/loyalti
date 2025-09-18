<?php

namespace MetaFox\Socialite\Listeners;

use MetaFox\Socialite\Support\Facades\Provider;

/**
 * Class SocialLoginFieldsListener.
 * @ignore
 * @codeCoverageIgnore
 */
class SocialLoginFieldsListener
{
    /**
     * @return array<string, mixed>
     */
    public function handle(string $resolution = 'web'): array
    {
        return Provider::buildFormFields($resolution);
    }
}
