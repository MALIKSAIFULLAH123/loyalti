<?php

namespace MetaFox\Rad\Providers;

use Illuminate\Support\ServiceProvider;

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
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if (app()->runningInConsole()) {
            $this->commands([
                \MetaFox\Rad\Console\Commands\MakeTestApi::class,
                \MetaFox\Rad\Console\Commands\MakeTestClass::class,
                \MetaFox\Rad\Console\Commands\MakePhpunitXml::class,
            ]);
        }
    }
}
