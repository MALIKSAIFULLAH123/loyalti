<?php

namespace MetaFox\Importer\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Importer\Repositories\BundleRepositoryInterface;
use MetaFox\Importer\Repositories\Eloquent\BundleRepository;
use MetaFox\Importer\Repositories\Eloquent\EntryRepository;
use MetaFox\Importer\Repositories\Eloquent\LogRepository;
use MetaFox\Importer\Repositories\EntryRepositoryInterface;
use MetaFox\Importer\Repositories\LogRepositoryInterface;

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
        BundleRepositoryInterface::class => BundleRepository::class,
        LogRepositoryInterface::class    => LogRepository::class,
        EntryRepositoryInterface::class  => EntryRepository::class,
    ];
}
