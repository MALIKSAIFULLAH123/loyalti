<?php

namespace MetaFox\Localize\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Localize\Contracts\CountryCitySupportContract;
use MetaFox\Localize\Contracts\CountrySupportContract;
use MetaFox\Localize\Contracts\CurrencySupportContract;
use MetaFox\Localize\Contracts\LanguageSupportContract;
use MetaFox\Localize\Models\Country;
use MetaFox\Localize\Models\Language as ModelsLanguage;
use MetaFox\Localize\Observers\CountryObserver;
use MetaFox\Localize\Observers\LanguageObserver;
use MetaFox\Localize\Repositories\CountryChildRepositoryInterface;
use MetaFox\Localize\Repositories\CountryCityRepositoryInterface;
use MetaFox\Localize\Repositories\CountryRepositoryInterface;
use MetaFox\Localize\Repositories\CurrencyRepositoryInterface;
use MetaFox\Localize\Repositories\Eloquent\CountryChildRepository;
use MetaFox\Localize\Repositories\Eloquent\CountryCityRepository;
use MetaFox\Localize\Repositories\Eloquent\CountryRepository;
use MetaFox\Localize\Repositories\Eloquent\CurrencyRepository;
use MetaFox\Localize\Repositories\Eloquent\LanguageRepository;
use MetaFox\Localize\Repositories\Eloquent\PhraseRepository;
use MetaFox\Localize\Repositories\Eloquent\TimezoneRepository;
use MetaFox\Localize\Repositories\LanguageRepositoryInterface;
use MetaFox\Localize\Repositories\PhraseRepositoryInterface;
use MetaFox\Localize\Repositories\TimezoneRepositoryInterface;
use MetaFox\Localize\Support\Country as CountrySupport;
use MetaFox\Localize\Support\CountryCity as CountryCitySupport;
use MetaFox\Localize\Support\Currency;
use MetaFox\Localize\Support\Language;
use MetaFox\Localize\Support\TranslationHelper;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Providers/PackageServiceProvider.stub.
 */

/**
 * Class PackageServiceProvider.
 *
 * @ignore
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageServiceProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    public array $singletons = [
        'phrases'                              => PhraseRepositoryInterface::class,
        'currency'                             => CurrencySupportContract::class,
        'translation'                          => TranslationHelper::class,
        CurrencySupportContract::class         => Currency::class,
        LanguageSupportContract::class         => Language::class,
        CountrySupportContract::class          => CountrySupport::class,
        CountryCitySupportContract::class      => CountryCitySupport::class,
        CurrencyRepositoryInterface::class     => CurrencyRepository::class,
        LanguageRepositoryInterface::class     => LanguageRepository::class,
        PhraseRepositoryInterface::class       => PhraseRepository::class,
        CountryRepositoryInterface::class      => CountryRepository::class,
        CountryChildRepositoryInterface::class => CountryChildRepository::class,
        TimezoneRepositoryInterface::class     => TimezoneRepository::class,
        CountryCityRepositoryInterface::class  => CountryCityRepository::class,
    ];

    public function boot(): void
    {
        Country::observe([
            CountryObserver::class,
            EloquentModelObserver::class,
        ]);

        ModelsLanguage::observe([
            LanguageObserver::class,
        ]);
    }
}
