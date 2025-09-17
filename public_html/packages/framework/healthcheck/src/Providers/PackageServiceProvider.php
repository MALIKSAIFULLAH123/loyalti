<?php

namespace MetaFox\HealthCheck\Providers;

use MetaFox\HealthCheck\Contracts\NoticeManager;
use Illuminate\Support\ServiceProvider;
use MetaFox\HealthCheck\Support\NoticeManager as SupportNoticeManager;

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
        NoticeManager::class => SupportNoticeManager::class,
    ];
}
