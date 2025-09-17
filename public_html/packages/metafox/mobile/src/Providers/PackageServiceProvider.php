<?php

namespace MetaFox\Mobile\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Mobile\Contracts\SupportInterface;
use MetaFox\Mobile\Repositories\AdMobConfigAdminRepositoryInterface;
use MetaFox\Mobile\Repositories\AdMobPageAdminRepositoryInterface;
use MetaFox\Mobile\Repositories\Eloquent\AdMobConfigAdminRepository;
use MetaFox\Mobile\Repositories\Eloquent\AdMobPageAdminRepository;
use MetaFox\Mobile\Supports\Support;

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
        AdMobConfigAdminRepositoryInterface::class => AdMobConfigAdminRepository::class,
        AdMobPageAdminRepositoryInterface::class   => AdMobPageAdminRepository::class,
        SupportInterface::class                    => Support::class,
    ];
}
