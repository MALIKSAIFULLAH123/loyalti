<?php

namespace MetaFox\Advertise\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use MetaFox\Advertise\Models\Advertise;
use MetaFox\Advertise\Models\Invoice;
use MetaFox\Advertise\Models\Placement;
use MetaFox\Advertise\Models\PlacementText;
use MetaFox\Advertise\Models\Sponsor;
use MetaFox\Advertise\Observers\AdvertiseObserver;
use MetaFox\Advertise\Observers\InvoiceObserver;
use MetaFox\Advertise\Observers\PlacementObserver;
use MetaFox\Advertise\Observers\SponsorObserver;
use MetaFox\Advertise\Policies\Handlers\CanPurchaseSponsor;
use MetaFox\Advertise\Policies\Handlers\CanPurchaseSponsorInFeed;
use MetaFox\Advertise\Policies\Handlers\CanShowSponsorLabel;
use MetaFox\Advertise\Policies\Handlers\CanSponsor;
use MetaFox\Advertise\Policies\Handlers\CanSponsorInFeed;
use MetaFox\Advertise\Policies\Handlers\CanUnsponsor;
use MetaFox\Advertise\Policies\Handlers\CanUnsponsorInFeed;
use MetaFox\Advertise\Repositories\AdvertiseHideRepositoryInterface;
use MetaFox\Advertise\Repositories\AdvertiseRepositoryInterface;
use MetaFox\Advertise\Repositories\CountryRepositoryInterface;
use MetaFox\Advertise\Repositories\Eloquent\AdvertiseHideRepository;
use MetaFox\Advertise\Repositories\Eloquent\AdvertiseRepository;
use MetaFox\Advertise\Repositories\Eloquent\CountryRepository;
use MetaFox\Advertise\Repositories\Eloquent\GenderRepository;
use MetaFox\Advertise\Repositories\Eloquent\InvoiceRepository;
use MetaFox\Advertise\Repositories\Eloquent\LanguageRepository;
use MetaFox\Advertise\Repositories\Eloquent\PlacementRepository;
use MetaFox\Advertise\Repositories\Eloquent\ReportRepository;
use MetaFox\Advertise\Repositories\Eloquent\SponsorRepository;
use MetaFox\Advertise\Repositories\Eloquent\StatisticRepository;
use MetaFox\Advertise\Repositories\GenderRepositoryInterface;
use MetaFox\Advertise\Repositories\InvoiceRepositoryInterface;
use MetaFox\Advertise\Repositories\LanguageRepositoryInterface;
use MetaFox\Advertise\Repositories\PlacementRepositoryInterface;
use MetaFox\Advertise\Repositories\ReportRepositoryInterface;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Advertise\Repositories\StatisticRepositoryInterface;
use MetaFox\Advertise\Services\Contracts\FilterConditionServiceInterface;
use MetaFox\Advertise\Services\Contracts\SponsorSettingServiceInterface;
use MetaFox\Advertise\Services\FilterConditionService;
use MetaFox\Advertise\Services\SponsorSettingService;
use MetaFox\Advertise\Support\Contracts\SupportInterface;
use MetaFox\Advertise\Support\Support;
use MetaFox\Platform\Facades\PolicyGate;
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
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        AdvertiseHideRepositoryInterface::class => AdvertiseHideRepository::class,
        AdvertiseRepositoryInterface::class     => AdvertiseRepository::class,
        CountryRepositoryInterface::class       => CountryRepository::class,
        GenderRepositoryInterface::class        => GenderRepository::class,
        InvoiceRepositoryInterface::class       => InvoiceRepository::class,
        LanguageRepositoryInterface::class      => LanguageRepository::class,
        PlacementRepositoryInterface::class     => PlacementRepository::class,
        SponsorRepositoryInterface::class       => SponsorRepository::class,
        StatisticRepositoryInterface::class     => StatisticRepository::class,
        ReportRepositoryInterface::class        => ReportRepository::class,
        SupportInterface::class                 => Support::class,
        SponsorSettingServiceInterface::class   => SponsorSettingService::class,
        FilterConditionServiceInterface::class  => FilterConditionService::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Advertise::observe([AdvertiseObserver::class]);
        Placement::observe([PlacementObserver::class]);
        PlacementText::observe([EloquentModelObserver::class]);
        Invoice::observe([InvoiceObserver::class]);
        Sponsor::observe([SponsorObserver::class]);

        $this->overrideSponsorRules();
    }

    protected function overrideSponsorRules(): void
    {
        if (!config('app.mfox_installed') || !app_active('metafox/advertise')) {
            return;
        }

        $rules = [
            ['sponsor', CanSponsor::class],
            ['purchaseSponsor', CanPurchaseSponsor::class],
            ['unsponsor', CanUnsponsor::class],
            ['sponsorInFeed', CanSponsorInFeed::class],
            ['purchaseSponsorInFeed', CanPurchaseSponsorInFeed::class],
            ['unsponsorInFeed', CanUnsponsorInFeed::class],
            ['showSponsorLabel', CanShowSponsorLabel::class],
        ];

        foreach ($rules as $rule) {
            Gate::define($rule[0], "{$rule[1]}@check");
            PolicyGate::addRule($rule[0], $rule[1]);
        }
    }
}
