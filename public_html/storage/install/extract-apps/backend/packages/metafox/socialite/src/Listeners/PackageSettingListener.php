<?php

namespace MetaFox\Socialite\Listeners;

use MetaFox\Platform\Support\BasePackageSettingListener;
use MetaFox\Socialite\Providers\FacebookServiceProvider;
use MetaFox\Socialite\Providers\GoogleServiceProvider;
use MetaFox\Socialite\Providers\TikTokServiceProvider;

/**
 * Class PackageSettingListener.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSettingListener extends BasePackageSettingListener
{
    private array $providers = [
        'SocialiteProviders\Apple\AppleExtendSocialite',
        TikTokServiceProvider::class,
        FacebookServiceProvider::class,
        GoogleServiceProvider::class,
    ];

    public function getSiteSettings(): array
    {
        return [
            'services.tiktok'               => [
                'module_id' => 'core',
                'is_public' => 0,
                'value'     => [],
            ],
            'prompt_users_to_set_passwords' => ['value' => 1],
        ];
    }

    public function getEvents(): array
    {
        return [
            'socialite.social_account.callback'                   => [
                SocialAccountCallbackListener::class,
            ],
            'socialite.social_account.request'                    => [
                SocialAccountRequestListener::class,
            ],
            'socialite.login_fields'                              => [
                SocialLoginFieldsListener::class,
            ],
            'socialite.provider.config'                           => [
                AppleProviderConfigListener::class,
                TiktokProviderConfigListener::class,
            ],
            \SocialiteProviders\Manager\SocialiteWasCalled::class => $this->loadAdditionalHandlers(),
        ];
    }

    private function loadAdditionalHandlers(): array
    {
        $handlers = [];
        foreach ($this->providers as $provider) {
            if (!class_exists($provider)) {
                continue;
            }

            $handlers[] = "{$provider}@handle";
        }

        return $handlers;
    }
}
