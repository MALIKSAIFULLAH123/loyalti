<?php

namespace MetaFox\RegexRule\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\RegexRule\Contracts\Regex as ContractRegex;
use MetaFox\RegexRule\Support\Regex;

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
        ContractRegex::class => Regex::class,
    ];
}
