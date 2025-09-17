<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Socialite\Support;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Builder;
use MetaFox\Form\Mobile\Builder as MobileBuilder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Class Provider.
 */
class Provider
{
    public const FACEBOOK = 'facebook';
    public const GOOGLE   = 'google';
    public const APPLE    = 'apple';

    /**
     * @return array<string>
     */
    public function getEnabledProviders(): array
    {
        $providers = [];
        foreach (Settings::get('core.services', []) as $provider => $config) {
            if (!Arr::get($config, 'login_enabled')) {
                continue;
            }

            $providers[] = $provider;
        }

        return $providers;
    }

    /**
     * @param string $provider
     * @param string $resolution
     *
     * @return ?AbstractField
     */
    public function buildFormField(string $provider, string $resolution = 'web'): ?AbstractField
    {
        try {
            $method = "{$provider}LoginButton";

            return match ($resolution) {
                MetaFoxConstant::RESOLUTION_MOBILE => MobileBuilder::$method(),
                MetaFoxConstant::RESOLUTION_WEB    => Builder::$method(),
            };
        } catch (Exception) {
            // silent
        }

        return null;
    }

    /**
     * @param string $resolution
     *
     * @return array<AbstractField>
     */
    public function buildFormFields(string $resolution = 'web'): array
    {
        $fields = [];
        foreach ($this->getEnabledProviders() as $provider) {
            $field = $this->buildFormField($provider, $resolution);
            if (empty($field)) {
                continue;
            }

            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * @return array<mixed>
     */
    public function getProviderSettings(): array
    {
        $settings  = [];
        $providers = $this->getEnabledProviders();
        foreach ($providers as $provider) {
            $settings[$provider] = array_filter(Settings::get("core.services.{$provider}", []), function ($key) {
                return !Str::contains($key, ['secret', 'private'], true);
            }, ARRAY_FILTER_USE_KEY);
        }

        return $settings;
    }
}
