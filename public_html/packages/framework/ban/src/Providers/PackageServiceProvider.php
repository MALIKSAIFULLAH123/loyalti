<?php

namespace MetaFox\Ban\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Ban\Contracts\SupportInterface;
use MetaFox\Ban\Models\BanRule;
use MetaFox\Ban\Repositories\BanRuleRepositoryInterface;
use MetaFox\Ban\Repositories\Eloquent\BanRuleRepository;
use MetaFox\Ban\Supports\Support;
use MetaFox\Platform\Support\EloquentModelObserver;

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
        BanRuleRepositoryInterface::class => BanRuleRepository::class,
        SupportInterface::class           => Support::class,
        'ban'                             => Support::class,
    ];

    public function boot(): void
    {
        BanRule::observe([
            EloquentModelObserver::class,
        ]);
    }
}
