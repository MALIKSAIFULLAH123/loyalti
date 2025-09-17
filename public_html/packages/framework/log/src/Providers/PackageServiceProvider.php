<?php

namespace MetaFox\Log\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Log\Repositories\Eloquent\LogMessageRepository;
use MetaFox\Log\Repositories\LogMessageRepositoryInterface;

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
        LogMessageRepositoryInterface::class => LogMessageRepository::class,
    ];
}
