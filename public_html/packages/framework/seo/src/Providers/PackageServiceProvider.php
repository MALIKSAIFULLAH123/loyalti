<?php

namespace MetaFox\SEO\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\SEO\Repositories\Eloquent\MetaRepository;
use MetaFox\SEO\Repositories\Eloquent\SchemaRepository;
use MetaFox\SEO\Repositories\MetaRepositoryInterface;
use MetaFox\SEO\Repositories\SchemaRepositoryInterface;

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
        MetaRepositoryInterface::class   => MetaRepository::class,
        SchemaRepositoryInterface::class => SchemaRepository::class,
    ];
}
