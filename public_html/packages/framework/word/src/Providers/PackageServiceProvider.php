<?php

namespace MetaFox\Word\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Word\Repositories\BlockRepositoryInterface;
use MetaFox\Word\Repositories\Eloquent\BlockRepository;

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
        BlockRepositoryInterface::class => BlockRepository::class,
    ];
}
