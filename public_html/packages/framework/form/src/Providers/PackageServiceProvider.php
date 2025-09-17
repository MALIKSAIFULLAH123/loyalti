<?php

namespace MetaFox\Form\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use MetaFox\Form\Support\HtmlFormBuilder;
use MetaFox\Form\Support\MobileFormBuilder;

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
     * @var string[]
     */
    public array $singletons = [
        HtmlFormBuilder::class   => HtmlFormBuilder::class,
        MobileFormBuilder::class => MobileFormBuilder::class,
    ];
}
