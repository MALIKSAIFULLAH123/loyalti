<?php

namespace MetaFox\Layout\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Layout\Repositories\BuildRepositoryInterface;
use MetaFox\Layout\Repositories\Eloquent\BuildRepository;
use MetaFox\Layout\Repositories\Eloquent\VariantRepository;
use MetaFox\Layout\Repositories\Eloquent\RevisionRepository;
use MetaFox\Layout\Repositories\Eloquent\SnippetRepository;
use MetaFox\Layout\Repositories\Eloquent\ThemeRepository;
use MetaFox\Layout\Repositories\VariantRepositoryInterface;
use MetaFox\Layout\Repositories\RevisionRepositoryInterface;
use MetaFox\Layout\Repositories\SnippetRepositoryInterface;
use MetaFox\Layout\Repositories\ThemeRepositoryInterface;

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
        SnippetRepositoryInterface::class  => SnippetRepository::class,
        BuildRepositoryInterface::class    => BuildRepository::class,
        RevisionRepositoryInterface::class => RevisionRepository::class,
        ThemeRepositoryInterface::class    => ThemeRepository::class,
        VariantRepositoryInterface::class  => VariantRepository::class,
    ];
}
