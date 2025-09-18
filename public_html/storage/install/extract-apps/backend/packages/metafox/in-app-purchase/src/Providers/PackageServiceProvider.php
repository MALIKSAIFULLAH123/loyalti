<?php

namespace MetaFox\InAppPurchase\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\InAppPurchase\Repositories\Eloquent\GoogleServiceAccountRepository;
use MetaFox\InAppPurchase\Repositories\Eloquent\OrderRepository;
use MetaFox\InAppPurchase\Repositories\Eloquent\ProductRepository;
use MetaFox\InAppPurchase\Repositories\GoogleServiceAccountRepositoryInterface;
use MetaFox\InAppPurchase\Repositories\OrderRepositoryInterface;
use MetaFox\InAppPurchase\Repositories\ProductRepositoryInterface;

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
        GoogleServiceAccountRepositoryInterface::class => GoogleServiceAccountRepository::class,
        ProductRepositoryInterface::class              => ProductRepository::class,
        OrderRepositoryInterface::class                => OrderRepository::class,
    ];
}
