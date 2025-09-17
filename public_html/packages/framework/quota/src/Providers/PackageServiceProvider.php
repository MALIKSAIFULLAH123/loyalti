<?php

namespace MetaFox\QuotaControl\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\QuotaControl\Facades\QuotaControl;

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
        'quota' => QuotaControl::class,
    ];
}
