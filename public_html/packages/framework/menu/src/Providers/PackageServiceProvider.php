<?php

namespace MetaFox\Menu\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Menu\Repositories\Eloquent\MenuItemRepository;
use MetaFox\Menu\Repositories\Eloquent\MenuRepository;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Menu\Repositories\MenuRepositoryInterface;

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
        'menu'                             => MenuRepositoryInterface::class,
        MenuRepositoryInterface::class     => MenuRepository::class,
        MenuItemRepositoryInterface::class => MenuItemRepository::class,
    ];
}
