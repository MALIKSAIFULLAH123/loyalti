<?php

namespace MetaFox\Rewrite\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Rewrite\Repositories\Eloquent\RuleRepository;
use MetaFox\Rewrite\Repositories\RuleRepositoryInterface;

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
        RuleRepositoryInterface::class => RuleRepository::class,
    ];
}
