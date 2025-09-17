<?php

namespace MetaFox\Report\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Platform\Support\EloquentModelObserver;
use MetaFox\Report\Models\ReportItem;
use MetaFox\Report\Models\ReportItemAggregate;
use MetaFox\Report\Models\ReportOwnerUser;
use MetaFox\Report\Models\ReportReason;
use MetaFox\Report\Observers\ReportItemObserver;
use MetaFox\Report\Observers\ReportOwnerUserObserver;
use MetaFox\Report\Repositories\Eloquent\ReportItemAdminRepository;
use MetaFox\Report\Repositories\Eloquent\ReportItemAggregateAdminRepository;
use MetaFox\Report\Repositories\Eloquent\ReportItemRepository;
use MetaFox\Report\Repositories\Eloquent\ReportOwnerRepository;
use MetaFox\Report\Repositories\Eloquent\ReportOwnerUserRepository;
use MetaFox\Report\Repositories\Eloquent\ReportReasonAdminRepository;
use MetaFox\Report\Repositories\Eloquent\ReportReasonRepository;
use MetaFox\Report\Repositories\ReportItemAdminRepositoryInterface;
use MetaFox\Report\Repositories\ReportItemAggregateAdminRepositoryInterface;
use MetaFox\Report\Repositories\ReportItemRepositoryInterface;
use MetaFox\Report\Repositories\ReportOwnerRepositoryInterface;
use MetaFox\Report\Repositories\ReportOwnerUserRepositoryInterface;
use MetaFox\Report\Repositories\ReportReasonAdminRepositoryInterface;
use MetaFox\Report\Repositories\ReportReasonRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        ReportItemRepositoryInterface::class               => ReportItemRepository::class,
        ReportItemAdminRepositoryInterface::class          => ReportItemAdminRepository::class,
        ReportReasonRepositoryInterface::class             => ReportReasonRepository::class,
        ReportReasonAdminRepositoryInterface::class        => ReportReasonAdminRepository::class,
        ReportOwnerRepositoryInterface::class              => ReportOwnerRepository::class,
        ReportOwnerUserRepositoryInterface::class          => ReportOwnerUserRepository::class,
        ReportItemAggregateAdminRepositoryInterface::class => ReportItemAggregateAdminRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            ReportItem::ENTITY_TYPE          => ReportItem::class,
            ReportItemAggregate::ENTITY_TYPE => ReportItemAggregate::class,
        ]);

        ReportOwnerUser::observe([ReportOwnerUserObserver::class]);
        ReportItem::observe([ReportItemObserver::class]);
        ReportReason::observe([EloquentModelObserver::class]);
        ReportItemAggregate::observe([EloquentModelObserver::class]);
    }
}
