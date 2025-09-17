<?php

namespace MetaFox\Socialite\Support\Facades;

use Illuminate\Support\Facades\Facade;
use MetaFox\Form\AbstractField;
use MetaFox\Socialite\Support\Provider as SupportProvider;

/**
 * Class Provider.
 *
 * @method static array<string>        getEnabledProviders()
 * @method static array<mixed>         getProviderSettings()
 * @method static AbstractField        buildFormField(string $provider, string $resolution = 'web')
 * @method static array<AbstractField> buildFormFields(string $resolution = 'web')
 * @see \MetaFox\Socialite\Support\Provider
 */
class Provider extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SupportProvider::class;
    }
}
