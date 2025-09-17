<?php

namespace MetaFox\Invite\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Invite\Models\Invite;
use MetaFox\Invite\Repositories\Eloquent\InviteAdminRepository;
use MetaFox\Invite\Repositories\Eloquent\InviteCodeRepository;
use MetaFox\Invite\Repositories\Eloquent\InviteRepository;
use MetaFox\Invite\Repositories\InviteAdminRepositoryInterface;
use MetaFox\Invite\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Invite\Repositories\InviteRepositoryInterface;
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
        InviteRepositoryInterface::class      => InviteRepository::class,
        InviteCodeRepositoryInterface::class  => InviteCodeRepository::class,
        InviteAdminRepositoryInterface::class => InviteAdminRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        /*
         * Register relation
         */
        Relation::morphMap([
            Invite::ENTITY_TYPE => Invite::class,
        ]);

        Invite::observe([EloquentModelObserver::class]);
    }
}
