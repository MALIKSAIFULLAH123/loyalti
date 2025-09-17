<?php

namespace MetaFox\Translation\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Translation\Contracts\TranslationGatewayManagerInterface;
use MetaFox\Translation\Models\TranslationGateway;
use MetaFox\Translation\Repositories\Eloquent\TranslationGatewayRepository;
use MetaFox\Translation\Repositories\Eloquent\TranslationServiceRepository;
use MetaFox\Translation\Repositories\Eloquent\TranslationTextRepository;
use MetaFox\Translation\Repositories\TranslationGatewayRepositoryInterface;
use MetaFox\Translation\Repositories\TranslationServiceRepositoryInterface;
use MetaFox\Translation\Repositories\TranslationTextRepositoryInterface;
use MetaFox\Translation\Support\TranslationGatewayManager;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Providers/PackageServiceProvider.stub
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
        TranslationGatewayRepositoryInterface::class => TranslationGatewayRepository::class,
        TranslationTextRepositoryInterface::class    => TranslationTextRepository::class,
        TranslationGatewayManagerInterface::class    => TranslationGatewayManager::class,
        TranslationServiceRepositoryInterface::class => TranslationServiceRepository::class,
    ];

    public function boot()
    {
        Relation::morphMap([
            TranslationGateway::ENTITY_TYPE => TranslationGateway::class,
        ]);
    }
}
