<?php

namespace MetaFox\Payment\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Payment\Contracts\GatewayManagerInterface;
use MetaFox\Payment\Models\Gateway;
use MetaFox\Payment\Models\Order;
use MetaFox\Payment\Models\Transaction;
use MetaFox\Payment\Repositories\Eloquent\GatewayRepository;
use MetaFox\Payment\Repositories\Eloquent\OrderAdminRepository;
use MetaFox\Payment\Repositories\Eloquent\OrderRepository;
use MetaFox\Payment\Repositories\Eloquent\TransactionRepository;
use MetaFox\Payment\Repositories\Eloquent\UserConfigurationRepository;
use MetaFox\Payment\Repositories\GatewayRepositoryInterface;
use MetaFox\Payment\Repositories\OrderAdminRepositoryInterface;
use MetaFox\Payment\Repositories\OrderRepositoryInterface;
use MetaFox\Payment\Repositories\TransactionRepositoryInterface;
use MetaFox\Payment\Repositories\UserConfigurationRepositoryInterface;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Payment\Support\GatewayManager;
use MetaFox\Payment\Support\Payment as SupportPayment;
use MetaFox\Payment\Support\Support;

/**
 * @ignore
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons =  [
        GatewayRepositoryInterface::class           => GatewayRepository::class,
        OrderRepositoryInterface::class             => OrderRepository::class,
        OrderAdminRepositoryInterface::class        => OrderAdminRepository::class,
        TransactionRepositoryInterface::class       => TransactionRepository::class,
        GatewayManagerInterface::class              => GatewayManager::class,
        UserConfigurationRepositoryInterface::class => UserConfigurationRepository::class,
        Payment::class                              => SupportPayment::class,
        'payment.support'                           => Support::class
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            Gateway::ENTITY_TYPE          => Gateway::class,
            Gateway::IMPORTER_ENTITY_TYPE => Gateway::class,
            Order::ENTITY_TYPE            => Order::class,
            Transaction::ENTITY_TYPE      => Transaction::class,
        ]);
    }
}
