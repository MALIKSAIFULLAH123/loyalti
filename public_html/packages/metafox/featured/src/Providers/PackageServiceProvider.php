<?php

namespace MetaFox\Featured\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Featured\Contracts\SupportInterface;
use MetaFox\Featured\Models\Item;
use MetaFox\Featured\Models\Package;
use MetaFox\Featured\Observers\ItemObserver;
use MetaFox\Featured\Observers\PackageObserver;
use MetaFox\Featured\Repositories\ApplicableItemTypeRepositoryInterface;
use MetaFox\Featured\Repositories\ApplicableRoleRepositoryInterface;
use MetaFox\Featured\Repositories\Eloquent\ApplicableItemTypeRepository;
use MetaFox\Featured\Repositories\Eloquent\ApplicableRoleRepository;
use MetaFox\Featured\Repositories\Eloquent\InvoiceRepository;
use MetaFox\Featured\Repositories\Eloquent\ItemRepository;
use MetaFox\Featured\Repositories\Eloquent\PackageRepository;
use MetaFox\Featured\Repositories\Eloquent\TransactionRepository;
use MetaFox\Featured\Repositories\InvoiceRepositoryInterface;
use MetaFox\Featured\Repositories\ItemRepositoryInterface;
use MetaFox\Featured\Repositories\PackageRepositoryInterface;
use MetaFox\Featured\Repositories\TransactionRepositoryInterface;
use MetaFox\Featured\Services\Contracts\SettingServiceInterface;
use MetaFox\Featured\Services\SettingService;
use MetaFox\Featured\Support\Support;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Providers/PackageServiceProvider.stub
 */

/**
 * Class PackageServiceProvider.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ApplicableItemTypeRepositoryInterface::class, ApplicableItemTypeRepository::class);
        $this->app->bind(ApplicableRoleRepositoryInterface::class, ApplicableRoleRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        $this->app->bind(PackageRepositoryInterface::class, PackageRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->bind(SupportInterface::class, Support::class);
        $this->app->bind(SettingServiceInterface::class, SettingService::class);
    }

    public function boot()
    {
        Item::observe([ItemObserver::class]);
        Package::observe([PackageObserver::class]);
    }
}
