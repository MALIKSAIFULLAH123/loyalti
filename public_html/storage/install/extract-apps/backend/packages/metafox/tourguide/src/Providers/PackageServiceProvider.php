<?php

namespace MetaFox\TourGuide\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Platform\Support\EloquentModelObserver;
use MetaFox\TourGuide\Models\Step;
use MetaFox\TourGuide\Models\TourGuide;
use MetaFox\TourGuide\Observers\StepObserver;
use MetaFox\TourGuide\Observers\TourGuideObserver;
use MetaFox\TourGuide\Repositories\Eloquent\TourGuideAdminRepository;
use MetaFox\TourGuide\Repositories\HiddenRepositoryInterface;
use MetaFox\TourGuide\Repositories\Eloquent\HiddenRepository;
use MetaFox\TourGuide\Repositories\Eloquent\StepRepository;
use MetaFox\TourGuide\Repositories\Eloquent\TourGuideRepository;
use MetaFox\TourGuide\Repositories\StepRepositoryInterface;
use MetaFox\TourGuide\Repositories\TourGuideAdminRepositoryInterface;
use MetaFox\TourGuide\Repositories\TourGuideRepositoryInterface;

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
        TourGuideRepositoryInterface::class      => TourGuideRepository::class,
        TourGuideAdminRepositoryInterface::class => TourGuideAdminRepository::class,
        StepRepositoryInterface::class           => StepRepository::class,
        HiddenRepositoryInterface::class         => HiddenRepository::class,
    ];

    public function boot()
    {
        TourGuide::observe([TourGuideObserver::class]);
        Step::observe([EloquentModelObserver::class, StepObserver::class]);
    }
}
