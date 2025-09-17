<?php

namespace MetaFox\FloodControl\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\FloodControl\Facades\FloodControl;

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
        'flood' => FloodControl::class,
    ];
}
