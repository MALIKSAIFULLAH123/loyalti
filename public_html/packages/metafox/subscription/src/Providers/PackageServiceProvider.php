<?php

namespace MetaFox\Subscription\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Platform\Support\EloquentModelObserver;
use MetaFox\Subscription\Contracts\SubscriptionCancelReasonContract;
use MetaFox\Subscription\Contracts\SubscriptionComparisonContract;
use MetaFox\Subscription\Contracts\SubscriptionInvoiceContract;
use MetaFox\Subscription\Contracts\SubscriptionPackageContract;
use MetaFox\Subscription\Models\SubscriptionCancelReason;
use MetaFox\Subscription\Models\SubscriptionComparison;
use MetaFox\Subscription\Models\SubscriptionInvoice;
use MetaFox\Subscription\Models\SubscriptionInvoiceTransaction;
use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Subscription\Models\SubscriptionPackageText;
use MetaFox\Subscription\Models\SubscriptionUserCancelReason;
use MetaFox\Subscription\Observers\SubscriptionComparisonObserver;
use MetaFox\Subscription\Observers\SubscriptionPackageObserver;
use MetaFox\Subscription\Observers\SubscriptionUserCancelReasonObserver;
use MetaFox\Subscription\Repositories\Eloquent\SubscriptionCancelReasonRepository;
use MetaFox\Subscription\Repositories\Eloquent\SubscriptionComparisonRepository;
use MetaFox\Subscription\Repositories\Eloquent\SubscriptionDependencyPackageRepository;
use MetaFox\Subscription\Repositories\Eloquent\SubscriptionInvoiceRepository;
use MetaFox\Subscription\Repositories\Eloquent\SubscriptionPackageRepository;
use MetaFox\Subscription\Repositories\Eloquent\SubscriptionPendingRegistrationUserRepository;
use MetaFox\Subscription\Repositories\SubscriptionCancelReasonRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionComparisonRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionDependencyPackageRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionPendingRegistrationUserRepositoryInterface;
use MetaFox\Subscription\Support\SubscriptionCancelReason as ReasonSupport;
use MetaFox\Subscription\Support\SubscriptionComparison as ComparisonSupport;
use MetaFox\Subscription\Support\SubscriptionInvoice as InvoiceSupport;
use MetaFox\Subscription\Support\SubscriptionPackage as PackageSupport;

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
        SubscriptionPackageContract::class                            => PackageSupport::class,
        SubscriptionPackageRepositoryInterface::class                 => SubscriptionPackageRepository::class,
        SubscriptionDependencyPackageRepositoryInterface::class       => SubscriptionDependencyPackageRepository::class,
        SubscriptionInvoiceRepositoryInterface::class                 => SubscriptionInvoiceRepository::class,
        SubscriptionCancelReasonRepositoryInterface::class            => SubscriptionCancelReasonRepository::class,
        SubscriptionComparisonRepositoryInterface::class              => SubscriptionComparisonRepository::class,
        SubscriptionPendingRegistrationUserRepositoryInterface::class => SubscriptionPendingRegistrationUserRepository::class,
        SubscriptionComparisonContract::class                         => ComparisonSupport::class,
        SubscriptionInvoiceContract::class                            => InvoiceSupport::class,
        SubscriptionCancelReasonContract::class                       => ReasonSupport::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        SubscriptionPackage::observe([EloquentModelObserver::class, SubscriptionPackageObserver::class]);
        SubscriptionPackageText::observe([EloquentModelObserver::class]);
        SubscriptionComparison::observe([SubscriptionComparisonObserver::class]);
        SubscriptionUserCancelReason::observe([SubscriptionUserCancelReasonObserver::class]);
        SubscriptionCancelReason::observe([EloquentModelObserver::class]);

        Relation::morphMap([
            SubscriptionInvoice::ENTITY_TYPE            => SubscriptionInvoice::class,
            SubscriptionInvoiceTransaction::ENTITY_TYPE => SubscriptionInvoiceTransaction::class,
        ]);
    }
}
