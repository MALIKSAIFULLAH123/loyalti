<?php

namespace MetaFox\ActivityPoint\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\ActivityPoint\Contracts\Support\PointConversionInterface;
use MetaFox\ActivityPoint\Models\ConversionRequest;
use MetaFox\ActivityPoint\Models\PackagePurchase;
use MetaFox\ActivityPoint\Models\PointPackage;
use MetaFox\ActivityPoint\Observers\ConversionRequestObserver;
use MetaFox\ActivityPoint\Support\PointConversion;
use MetaFox\Platform\Support\EloquentModelObserver;

/**
 * Class PackageServiceProvider.
 * @codeCoverageIgnore
 * @ignore
 */
class PackageServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $singletons = [
        // Repositories
        \MetaFox\ActivityPoint\Repositories\PointPackageRepositoryInterface::class        => \MetaFox\ActivityPoint\Repositories\Eloquent\PointPackageRepository::class,
        \MetaFox\ActivityPoint\Repositories\PointStatisticRepositoryInterface::class      => \MetaFox\ActivityPoint\Repositories\Eloquent\PointStatisticRepository::class,
        \MetaFox\ActivityPoint\Repositories\PointTransactionRepositoryInterface::class    => \MetaFox\ActivityPoint\Repositories\Eloquent\PointTransactionRepository::class,
        \MetaFox\ActivityPoint\Repositories\PointSettingRepositoryInterface::class        => \MetaFox\ActivityPoint\Repositories\Eloquent\PointSettingRepository::class,
        \MetaFox\ActivityPoint\Repositories\PurchasePackageRepositoryInterface::class     => \MetaFox\ActivityPoint\Repositories\Eloquent\PurchasePackageRepository::class,
        \MetaFox\ActivityPoint\Repositories\ConversionRequestRepositoryInterface::class   => \MetaFox\ActivityPoint\Repositories\Eloquent\ConversionRequestRepository::class,
        \MetaFox\ActivityPoint\Repositories\ConversionStatisticRepositoryInterface::class => \MetaFox\ActivityPoint\Repositories\Eloquent\ConversionStatisticRepository::class,
        \MetaFox\ActivityPoint\Contracts\Support\ActivityPoint::class                     => \MetaFox\ActivityPoint\Support\ActivityPoint::class,
        \MetaFox\ActivityPoint\Contracts\Support\PointSetting::class                      => \MetaFox\ActivityPoint\Support\PointSetting::class,
        \MetaFox\ActivityPoint\Contracts\Support\ActionType::class                        => \MetaFox\ActivityPoint\Support\ActionType::class,
        PointConversionInterface::class                                                   => PointConversion::class,
    ];

    public function boot(): void
    {
        /*
         * Register relation
         */
        Relation::morphMap([
            PointPackage::ENTITY_TYPE    => PointPackage::class,
            PackagePurchase::ENTITY_TYPE => PackagePurchase::class,
        ]);

        ConversionRequest::observe([ConversionRequestObserver::class]);
        PointPackage::observe([EloquentModelObserver::class]);
    }

    public function register()
    {
        $this->callAfterResolving('reducer', function ($reducer) {
            $reducer->register([
                \MetaFox\ActivityPoint\Support\LoadMissingPointStatistic::class,
            ]);
        });
    }
}
